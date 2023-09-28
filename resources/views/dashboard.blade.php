<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    {{ __("You're logged in!") }}

                    <h2 class="text-xl font-bold">Your link below:</h2>

                    <p class="text-blue-400"> {{ env('APP_URL') }}/register/{{ auth()->user()->id }}</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
