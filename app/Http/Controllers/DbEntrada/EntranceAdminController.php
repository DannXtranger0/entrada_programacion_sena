<?php

namespace App\Http\Controllers\DbEntrada;

use App\Http\Controllers\Controller;
use App\Models\DbEntrada\DayAvailable;
use App\Models\DbEntrada\Person;
use App\Models\DbEntrada\Position;
use App\Models\DbEntrada\User;
use App\Models\DbProgramacion\Person as DbProgramacionPerson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use phpDocumentor\Reflection\PseudoTypes\LowercaseString;

class EntranceAdminController extends Controller
{

    public function peopleIndex(Request $request)
    {
        $search = $request->input('search');

        $person = Person::with('position')->where(function($query) use ($search){
            $query->where('document_number','like',"%$search%")->orWhere('name','like',"%$search%");
        }
        )->paginate(20)->appends(['search'=>$search]);
            
        return view('pages.entrance.admin.people.people_index',['person'=> $person]);
    }

    public function peopleShow($id){
        
        $person = Person::with('days_available')->findOrFail($id);

        // $email = DbProgramacionPerson::where('document_number',$person->document_number)->pluck('email');
        return view('pages.entrance.admin.people.people_show',['person'=>$person]);
    }

    public function peopleCreate(){
        $positions = Position::pluck('position','id');
        $days_available = DayAvailable::all();
        return view('pages.entrance.admin.people.people_create',['positions'=> $positions, 'days_available'=>$days_available]);
    }

    public function peopleStore(Request $request){

        //Creacion de la persona
        $data = $request->validate([
            //Datos de la persona
           'id_position' => 'required',
           'document_number' => 'required',
           'name' => 'required',
           'start_date' => 'required|date',
           'end_date' => 'required|date|after_or_equal:start_date'
        ]);

    
        //Si ya la persona se registró, cancelar
        if(Person::where('document_number',$data['document_number'])->exists()){
            return redirect()->route('entrance.people.index')->with('message', 'Ya existe una persona en el centro con este numero de documento');
        }

        $person = Person::create($data);
        //Creacion del usuario si es aprendiz
        if(trim($person->position->position) === 'Aprendiz'){
                $user = User::create([
                'id_person' => $person->id,
                'user_name' => $person->document_number,
                'password' => bcrypt($person->document_number)
            ]);
            $user->assignRole('Aprendiz');
        }

        //Asignar los días que vendrá la persona

        $person->days_available()->sync($request->days);

        return redirect()->route('entrance.people.index')->with('message','Persona Registrada Correctamente');
        
    }

    public function peopleEdit($id){
        $person = Person::findOrFail($id);
        $positions = Position::all();
        $days_available =  DayAvailable::all();
        return view('pages.entrance.admin.people.people_edit',['person'=>$person,'positions'=>$positions,'days_available'=>$days_available]);
    }

    public function peopleUpdate(Request $request, $id){
        $data = $request->validate([
            'id_position' => 'required',
            'name' => 'required',
            'document_number' => 'required',
            'start_date' => 'required',
            'end_date' => 'required|date|after_or_equal:start_date'
        ]);

        $personToUpdate = Person::findOrFail($id);
        
        $personToUpdate->update([
            'id_position' => $data['id_position'],
            'name' => $data['name'],
            'document_number' => $data['document_number'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
        ]);
        $personToUpdate->days_available()->sync($request->days);

            return redirect()->route('entrance.people.index')->with('message', 'Datos actualizados exitosamente!');

    }

    public function peopleDelete($id){
        $person = Person::findOrFail($id);

        if($person){
            $person->days_available()->detach();
            if($person->user){
                $person->user->delete();
            }
            $person->delete();
            return redirect()->route('entrance.people.index')->with('message', 'Persona eliminada correctamente');

        }

        return redirect()->route('entrance.people.index')->with('message', 'Persona no encontrada');

    }




    public function storePeopleExcel(Request $request)
    {
        set_time_limit(600);
        ini_set('memory_limit', '2048M');

        try {
            Log::info('Iniciando importación de personas...');
            // Validación básica
            $data = $request->validate([
                'people' => 'required|array',
                'people.*.NUMERO_DOCUMENTO' => 'required|max:13',
                'people.*.NOMBRES' => 'nullable|string',
                'people.*.APELLIDOS' => 'nullable|string',
                'people.*.FECHA_INICIO' => 'nullable|date',
                'people.*.FECHA_FINALIZACION' => 'nullable|date|after_or_equal:people.*.FECHA_INICIO',
            ]);

            Log::info('Total registros recibidos:', ['cantidad' => count($data['people'])]);

            // Eliminar duplicados dentro del archivo Excel
            $people = collect($data['people'])->unique('NUMERO_DOCUMENTO')->values()->toArray();
            Log::info('Personas únicas en el archivo:', ['cantidad' => count($people)]);

            // Obtener los documentos que ya están en la BD
            $documentosExistentes = Person::pluck('document_number')->flip();
            Log::info('Total documentos en la BD:', ['cantidad' => count($documentosExistentes)]);

            $nuevosRegistros = 0;
            $errores = [];

            DB::beginTransaction();

            foreach ($people as $row) {
                try {
                    // Si el documento ya existe en db, lo ignoramos
                    if (isset($documentosExistentes[$row['NUMERO_DOCUMENTO']])) {
                        continue;
                    }

                    // Crear persona
                    $person = Person::create([
                        'document_number' => $row['NUMERO_DOCUMENTO'],
                        'id_position' => 3,
                        'name' => trim(($row['NOMBRES'] ?? '') . " " . ($row['APELLIDOS'] ?? '')),
                        'start_date' => $row['FECHA_INICIO'] ?? null,
                        'end_date' => $row['FECHA_FINALIZACION'] ?? null,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);

                    $days = [1,2,3,4,5];
                    $person->days_available()->sync($days);

                    // Crear usuario vinculado
                    User::create([
                        'id_person' => $person->id,
                        'user_name' => $person->document_number,
                        'password' => bcrypt($person->document_number),
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);


                    $nuevosRegistros++;
                    Log::info('Persona registrada:', ['document' => $person->document_number, 'name' => $person->name]);

                    // Agregar el documento al array de existentes para evitar futuros duplicados
                    $documentosExistentes[$row['NUMERO_DOCUMENTO']] = true;
                } catch (\Exception $e) {
                    // Guardar error sin detener el proceso
                    $errores[] = ['document' => $row['NUMERO_DOCUMENTO'], 'error' => $e->getMessage()];
                    Log::error('Error con el documento ' . $row['NUMERO_DOCUMENTO'] . ': ' . $e->getMessage());
                }
            }

            DB::commit();
            Log::info('Importación finalizada.', ['total_registrados' => $nuevosRegistros, 'total_errores' => count($errores)]);

            return response()->json([
                'success' => true,
                'message' => 'Importación completada',
                'registrados' => $nuevosRegistros,
                'errores' => $errores
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en la importación: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error en la importación',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}
