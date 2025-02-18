<x-layout>
    {{-- Archivo CSS de la pagina --}}
    <x-slot:page_style>css/pages/assistance/assistance_index.css</x-slot:page_style>
    {{-- Titulo de la pagina --}}
    <x-slot:title>CAA</x-slot:title>
    {{-- Header - Navbar --}}
    <x-entrance_navbar></x-entrance_navbar>

    <div class="container">
        <h1 class="text-center mb-4">
            Lista de Asistencias - {{ now()->format('Y-m-d') }}
        </h1>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3>Cantidad de personas: <span class="badge bg-primary">{{ $formattedPersons->count() }}</span></h3>

            <form method="GET" action="{{ route('entrance.assistance.index') }}" class="d-flex align-items-center">
                <!-- Select para filtrar por posición -->
                <select name="position_id" class="form-select me-2" onchange="this.form.submit()">
                    <option value="">Todos los puestos</option>
                    @foreach ($positions as $position)
                        <option value="{{ $position->id }}" {{ request('position_id') == $position->id ? 'selected' : '' }}>
                            {{ $position->position }}
                        </option>
                    @endforeach
                </select>

                <!-- Campo de fecha -->
                <input type="date" name="filter_date" class="form-control me-2" max="{{ now()->toDateString() }}" value="{{ request('filter_date') }}" onchange="this.form.submit()">

                <!-- Formulario de búsqueda -->
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Buscar por nombre o documento" value="{{ request('search') }}">
                    <button class="btn btn-outline-secondary" type="submit">Buscar</button>
                </div>
            </form>
        </div>

        <table class="table table-bordered table-striped">
            <thead class="thead-dark">
                <tr>
                    <th>Nombre</th>
                    <th>Puesto</th>
                    <th>Asistencia por día</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($formattedPersons as $person)
                    <tr>
                        <td>{{ $person['name'] }}</td>
                        <td>{{ $person['position'] }}</td>
                        <td>
                            @forelse ($person['daily_data'] as $data)
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        Entrada: {{ $data['entrada'] ?? 'Sin registro' }}
                                    </div>
                                    <div>
                                        Salida: {{ $data['salida'] ?? 'No ha escaneado salida' }}
                                    </div>
                                </div>
                                @if (!$loop->last)
                                    <hr>
                                @endif
                            @empty
                                <div class="text-center">Sin asistencias registradas para este día.</div>
                            @endforelse
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center">No se encontraron asistencias.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</x-layout>
