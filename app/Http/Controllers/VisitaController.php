<?php

namespace App\Http\Controllers;

use App\Mail\SolicitudVisitaRecibida;
use App\Models\SolicitudVisita;
use App\Models\Vehiculo;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

// "Agendar visita": el comprador elige día y hora en la ficha del auto, la solicitud se guarda y
// le llega al vendedor por correo y en su panel, desde donde le responde por WhatsApp.
class VisitaController extends Controller
{
    public const DIAS = 14;
    public const HORAS = ['09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00', '18:00', '19:00'];

    public function guardar(Request $request, Vehiculo $vehiculo)
    {
        abort_unless($vehiculo->estaVisible(), 404);
        $volver = route('vehiculos.show', $vehiculo) . '#agendar';

        // Campo trampa para bots: si viene lleno, se responde como si todo saliera bien.
        if ($request->filled('sitio_web')) {
            return redirect()->to($volver)->with('visita_ok', true);
        }

        // Celular chileno: se aceptan "9 1234 5678", "+56 9 1234 5678", etc. y se guarda como 569XXXXXXXX.
        $telefono = preg_replace('/\D/', '', (string) $request->input('telefono'));
        if (strlen($telefono) === 9 && str_starts_with($telefono, '9')) {
            $telefono = '56' . $telefono;
        }
        $request->merge(['telefono_normalizado' => $telefono]);

        $hoy = now('America/Santiago')->startOfDay();
        $validador = Validator::make($request->all(), [
            'fecha' => ['required', 'date_format:Y-m-d', 'after_or_equal:' . $hoy->toDateString(), 'before:' . $hoy->copy()->addDays(self::DIAS)->toDateString()],
            'hora' => ['required', 'in:' . implode(',', self::HORAS)],
            'nombre' => ['required', 'string', 'max:60'],
            'telefono_normalizado' => ['required', 'regex:/^569\d{8}$/'],
        ], [
            'telefono_normalizado.required' => 'Ingresa tu WhatsApp para que el vendedor te responda.',
            'telefono_normalizado.regex' => 'Ingresa un celular válido, por ejemplo 9 1234 5678.',
            'fecha.*' => 'Elige un día del calendario.',
            'hora.*' => 'Elige una hora.',
        ], ['nombre' => 'tu nombre']);

        // Para hoy, la hora tiene que ser al menos 1 hora más tarde que ahora.
        $validador->after(function ($v) use ($request) {
            if ($v->errors()->isEmpty()) {
                $momento = Carbon::parse($request->fecha . ' ' . $request->hora, 'America/Santiago');
                if ($momento->lt(now('America/Santiago')->addHour())) {
                    $v->errors()->add('hora', 'Esa hora ya pasó, elige otra.');
                }
            }
        });

        if ($validador->fails()) {
            return redirect()->to($volver)->withErrors($validador, 'visita')->withInput();
        }

        $solicitud = SolicitudVisita::create([
            'vehiculo_id' => $vehiculo->id,
            'user_id' => $vehiculo->user_id,
            'nombre' => trim($request->nombre),
            'telefono' => $telefono,
            'fecha' => $request->fecha,
            'hora' => $request->hora,
        ]);

        // Si el correo falla, la solicitud igual queda en el panel del vendedor.
        try {
            if ($correo = $vehiculo->usuario?->email) {
                Mail::to($correo)->send(new SolicitudVisitaRecibida($solicitud));
            }
        } catch (\Throwable $e) {
            Log::error('No se pudo enviar el aviso de solicitud de visita', ['solicitud' => $solicitud->id, 'error' => $e->getMessage()]);
        }

        return redirect()->to($volver)->with('visita_ok', true);
    }

    public function atender(Request $request, SolicitudVisita $solicitud)
    {
        abort_unless($solicitud->user_id === $request->user()->id, 403);
        $solicitud->update(['estado' => 'atendida']);

        return redirect()->route('panel')->with('ok', 'Solicitud de visita marcada como atendida.');
    }
}
