<x-layout>
    <x-slot:page_style>css/pages/start_page.css</x:slot-page_style>
    <x-slot:title>CAA</x:slot-title>

    <h1>Pinche Mugroso, fuera de aki</h1>

    <form action="{{route('entrance-logout')}}" method="POST">
        @csrf
        <x-button type="submit">Logout</x-button>
    </form>
</x-layout>