<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'telefono' => ['required', 'string', 'max:20', 'regex:/^[+\d\s]{8,20}$/'],
            'region' => ['required', 'string', \Illuminate\Validation\Rule::in(array_keys(config('regiones')))],
            'ciudad' => ['required', 'string', \Illuminate\Validation\Rule::in(config('regiones.' . $request->input('region'), []))],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'region.required' => 'Selecciona tu región.',
            'region.in' => 'Selecciona una región de la lista.',
            'ciudad.required' => 'Selecciona tu ciudad o comuna.',
            'ciudad.in' => 'Selecciona una comuna de la región elegida.',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'telefono_whatsapp' => preg_replace('/\s+/', '', $request->telefono),
            'region' => $request->region,
            'comuna' => $request->ciudad,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('panel', absolute: false))->with('bienvenida', true);
    }
}
