<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div>
            <x-input-label for="name" value="Nombre" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="email" value="Correo" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="telefono" value="Teléfono (WhatsApp)" />
            <x-text-input id="telefono" class="block mt-1 w-full" type="tel" name="telefono" :value="old('telefono')" required autocomplete="tel" placeholder="+56 9 1234 5678" />
            <x-input-error :messages="$errors->get('telefono')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="region" value="Región" />
            <select id="region" name="region" required class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" onchange="llenarComunas()">
                <option value="">Selecciona tu región</option>
                @foreach (array_keys(config('regiones')) as $r)
                    <option value="{{ $r }}" @selected(old('region') === $r)>{{ $r }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('region')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="ciudad" value="Ciudad / comuna" />
            <select id="ciudad" name="ciudad" required data-seleccionada="{{ old('ciudad') }}" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                <option value="">Selecciona una región primero</option>
            </select>
            <x-input-error :messages="$errors->get('ciudad')" class="mt-2" />
        </div>
        <script>
            // La lista de comunas cambia según la región elegida.
            const comunasPorRegion = @json(config('regiones'));
            function llenarComunas() {
                const region = document.getElementById('region').value;
                const select = document.getElementById('ciudad');
                const elegida = select.value || select.dataset.seleccionada;
                select.innerHTML = '';
                const inicial = new Option(region ? 'Selecciona tu comuna' : 'Selecciona una región primero', '');
                select.add(inicial);
                (comunasPorRegion[region] || []).forEach(c => select.add(new Option(c, c, false, c === elegida)));
                select.disabled = !region;
            }
            llenarComunas();
        </script>

        <div class="mt-4">
            <x-input-label for="password" value="Crea tu contraseña" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
            <p class="mt-1 text-xs text-gray-500">Mínimo 8 caracteres.</p>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password_confirmation" value="Repite tu contraseña" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <button type="submit" class="btn btn-acento btn-block" style="margin-top:22px;padding:12px">Crear cuenta</button>

        <p class="mt-4 text-center text-sm text-gray-600">
            ¿Ya tienes cuenta? <a href="{{ route('login') }}" class="font-semibold" style="color:var(--acento)">Inicia sesión</a>
        </p>
    </form>
</x-guest-layout>
