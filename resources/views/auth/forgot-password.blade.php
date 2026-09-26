<x-guest-layout>
    @php
        $numeroWhatsapp = preg_replace('/\D/', '', config('autoruta.contacto_whatsapp'));
        // Con "log" o "array" los correos no salen del servidor: se ofrece solo la ayuda por WhatsApp.
        $correoActivo = ! in_array(config('mail.default'), ['log', 'array'], true);
    @endphp

    <h2 style="text-align:center;font-size:18px;font-weight:700;margin:0 0 8px">¿Olvidaste tu contraseña?</h2>

    @if ($correoActivo)
        <p class="mb-4 text-sm text-gray-600">Ingresa tu correo y te enviaremos un enlace para crear una nueva.</p>
        <x-auth-session-status class="mb-4" :status="session('status')" />
        <form method="POST" action="{{ route('password.email') }}">
            @csrf
            <div>
                <x-input-label for="email" value="Correo" />
                <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>
            <button type="submit" class="btn btn-acento btn-block" style="margin-top:18px;padding:12px">Enviar enlace</button>
        </form>
        <p class="mt-4 text-sm text-gray-600" style="text-align:center">¿No te llega el correo?</p>
    @else
        <p class="mb-4 text-sm text-gray-600" style="text-align:center">Escríbenos por WhatsApp desde el número con que te registraste y te enviaremos una contraseña temporal para que puedas entrar.</p>
    @endif

    <a href="https://wa.me/{{ $numeroWhatsapp }}?text={{ urlencode('Hola, olvidé mi contraseña de AutoRuta. Mi correo registrado es: ') }}" target="_blank" rel="noopener"
       class="btn btn-block" style="margin-top:10px;padding:12px;background:#25D366;color:#fff">Recuperar por WhatsApp</a>

    <p class="mt-4 text-center text-sm text-gray-600">
        <a href="{{ route('login') }}" class="font-semibold" style="color:var(--acento)">← Volver a iniciar sesión</a>
    </p>
</x-guest-layout>
