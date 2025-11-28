<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Usuario;
use App\Models\Membresia;
use App\Models\RegistroAcceso;
use App\Models\Configuracion;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class GymStatusController extends Controller
{   
    //aforo
    public function getAforo()
    {
        try {
            $personasDentro = Usuario::where('is_inside', 1)->count();

            $config = Configuracion::where('clave', 'aforo_maximo')->first();
            
            $capacidadMaxima = $config ? (int)$config->valor : 0; 

            $porcentaje = 0;
            if ($capacidadMaxima > 0) {
                $porcentaje = ($personasDentro / $capacidadMaxima) * 100;
            }

            return response()->json([
                'cantidad' => $personasDentro,
                'porcentaje' => round($porcentaje),
                'maximo' => $capacidadMaxima
            ]);

        } catch (\Exception $e) {
            return response()->json(['cantidad' => 0, 'porcentaje' => 0, 'maximo' => 0]);
        }
    }

    //HEADER
    public function getHeaderData(Request $request)
    {
        $usuario = $request->user();
        return response()->json(['nombre' => $usuario ? $usuario->nombre_comp : 'Invitado']);
    }

    public function getUserProfile(Request $request)
    {
        $usuario = $request->user();
        
        $asistencias = RegistroAcceso::where('usuario_id', $usuario->id)
                        ->where('acceso', 1)
                        ->whereMonth('fecha', Carbon::now()->month) 
                        ->pluck('fecha')
                        ->map(function($date) {
                            return Carbon::parse($date)->format('Y-m-d');
                        })
                        ->unique()
                        ->values();

        // Membresía
        $membresia = Membresia::with('plan') 
                        ->where('usuario_id', $usuario->id)
                        ->orderBy('id', 'desc') 
                        ->first();

        $datosPlan = [
            'nombre' => 'Sin Membresía',
            'inicio' => '--',
            'inicio_raw' => null,
            'fin' => '--',
            'activo' => false,
            'dias_restantes' => 0, 
            'tipo_alerta' => 'none',
            'mensaje_alerta' => ''
        ];

        if ($membresia) {
            try {
                $inicio = Carbon::parse($membresia->fecha_ini);
                $fin = Carbon::parse($membresia->fecha_fin)->endOfDay();
                $hoy = Carbon::now();

                $esVigente = !$fin->isPast();
                $diasRestantes = (int) $hoy->diffInDays($fin, false);
                
                if (!$esVigente && $diasRestantes >= 0) $diasRestantes = -1;

                $nombrePlan = $membresia->plan ? $membresia->plan->nombre : 'Plan General';

                $datosPlan['nombre'] = $nombrePlan;
                $datosPlan['inicio'] = $inicio->format('d M Y');
                $datosPlan['inicio_raw'] = $inicio->format('Y-m-d');
                $datosPlan['fin'] = $fin->format('d M Y');
                $datosPlan['activo'] = $esVigente;
                $datosPlan['dias_restantes'] = (int)$diasRestantes;

                if (!$esVigente) {
                    $datosPlan['tipo_alerta'] = 'expired';
                    $datosPlan['mensaje_alerta'] = "Tu $nombrePlan venció hace " . abs((int)$diasRestantes) . " días.";
                } elseif ($diasRestantes <= 3) {
                    $datosPlan['tipo_alerta'] = 'urgent'; 
                    $datosPlan['mensaje_alerta'] = "¡Urgente! Tu $nombrePlan vence en " . (int)$diasRestantes . " días.";
                } elseif ($diasRestantes <= 10) {
                    $datosPlan['tipo_alerta'] = 'warning'; 
                    $datosPlan['mensaje_alerta'] = "Aviso: Te quedan " . (int)$diasRestantes . " días de membresía.";
                }
            } catch (\Exception $e) {
                Log::error("Error fechas perfil: " . $e->getMessage());
            }
        }

        return response()->json([
            'asistencias' => $asistencias,
            'membresia' => $datosPlan
        ]);
    }

    // 4. ESTADÍSTICAS
    public function getBusynessStats()
    {
        try {
            $hoy = Carbon::now();
            $diaSemanaMysql = $hoy->dayOfWeek + 1; 

            $stats = RegistroAcceso::selectRaw('HOUR(fecha) as hora, COUNT(*) as total')
                        ->where('fecha', '>=', $hoy->copy()->subDays(60))
                        ->where('acceso', 1) 
                        ->whereRaw('DAYOFWEEK(fecha) = ?', [$diaSemanaMysql])
                        ->whereRaw('HOUR(fecha) BETWEEN 6 AND 22') 
                        ->groupBy('hora')
                        ->orderBy('total', 'desc')
                        ->get();

            if ($stats->isEmpty()) {
                return response()->json(['peak' => [], 'quiet' => [], 'histogram' => []]);
            }

            $histogram = [];
            for ($i = 6; $i <= 22; $i++) {
                $found = $stats->firstWhere('hora', $i);
                $histogram[] = [
                    'hour' => $i,
                    'count' => $found ? $found->total : 0
                ];
            }

            $formatTime = function ($hora) {
                return Carbon::createFromTime($hora, 0)->format('g A'); 
            };

            $peakHours = $stats->take(3)->sortBy('hora')->map(function ($item) use ($formatTime) {
                return $formatTime($item->hora);
            })->values();

            $quietHours = $stats->sortBy('total')->take(3)->sortBy('hora')->map(function ($item) use ($formatTime) {
                return $formatTime($item->hora);
            })->values();

            return response()->json([
                'histogram' => $histogram,
                'peak' => $peakHours,  
                'quiet' => $quietHours 
            ]);
        } catch (\Exception $e) {
            return response()->json(['peak' => [], 'quiet' => [], 'histogram' => []]);
        }
    }

    // 5. LOGOUT
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Sesión cerrada']);
    }
}