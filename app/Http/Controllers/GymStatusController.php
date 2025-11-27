<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Usuario;
use App\Models\Membresia;
use App\Models\RegistroAcceso;
use Carbon\Carbon;

class GymStatusController extends Controller
{
    // 1. AFORO
    public function getAforo()
    {
        $personasDentro = Usuario::where('is_inside', 1)->count();
        $capacidadMaxima = 50; 
        $porcentaje = ($capacidadMaxima > 0) ? ($personasDentro / $capacidadMaxima) * 100 : 0;

        return response()->json([
            'cantidad' => $personasDentro,
            'porcentaje' => round($porcentaje)
        ]);
    }

    // 2. HEADER
    public function getHeaderData(Request $request)
    {
        $usuario = $request->user();
        return response()->json(['nombre' => $usuario ? $usuario->nombre_comp : 'Invitado']);
    }

    // 3. PERFIL
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

        $membresia = Membresia::with('plan') 
                        ->where('usuario_id', $usuario->id)
                        ->orderBy('fecha_fin', 'desc')
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
            $inicio = Carbon::parse($membresia->fecha_ini);
            $fin = Carbon::parse($membresia->fecha_fin);
            $hoy = Carbon::now()->startOfDay();

            $diasRestantes = $hoy->diffInDays($fin, false); 
            $esVigente = $diasRestantes >= 0;
            $nombrePlan = $membresia->plan ? $membresia->plan->nombre : 'Plan General';

            $datosPlan['nombre'] = $nombrePlan;
            $datosPlan['inicio'] = $inicio->format('d M Y');
            $datosPlan['inicio_raw'] = $inicio->format('Y-m-d');
            $datosPlan['fin'] = $fin->format('d M Y');
            $datosPlan['activo'] = $esVigente;
            $datosPlan['dias_restantes'] = (int)$diasRestantes;

            if ($diasRestantes < 0) {
                $datosPlan['tipo_alerta'] = 'expired';
                $datosPlan['mensaje_alerta'] = "Tu $nombrePlan venció hace " . abs($diasRestantes) . " días.";
            } elseif ($diasRestantes <= 3) {
                $datosPlan['tipo_alerta'] = 'warning';
                $datosPlan['mensaje_alerta'] = "Tu $nombrePlan vence en $diasRestantes días.";
            }
        }

        return response()->json([
            'asistencias' => $asistencias,
            'membresia' => $datosPlan
        ]);
    }

    public function getBusynessStats()
    {
        $hoy = Carbon::now();
        $diaSemanaMysql = $hoy->dayOfWeek + 1; // 1=Domingo, 7=Sábado

        $stats = RegistroAcceso::selectRaw('HOUR(fecha) as hora, COUNT(*) as total')
                    ->where('fecha', '>=', $hoy->copy()->subDays(60))
                    ->where('acceso', 1) 
                    ->whereRaw('DAYOFWEEK(fecha) = ?', [$diaSemanaMysql])
                    ->whereRaw('HOUR(fecha) BETWEEN 6 AND 22') 
                    ->groupBy('hora')
                    ->orderBy('total', 'desc') // Ordenamos por cantidad de gente primero
                    ->get();

        if ($stats->isEmpty()) {
            return response()->json(['peak' => [], 'quiet' => []]);
        }

        // Helper para formato
        $formatTime = function ($hora) {
            return Carbon::createFromTime($hora, 0)->format('g A'); 
        };

        $peakHours = $stats->take(3)
                           ->sortBy('hora')
                           ->map(function ($item) use ($formatTime) {
                               return $formatTime($item->hora);
                           })->values();

        $quietHours = $stats->sortBy('total')
                            ->take(3)
                            ->sortBy('hora')
                            ->map(function ($item) use ($formatTime) {
                                return $formatTime($item->hora);
                            })->values();

        return response()->json([
            'peak' => $peakHours,  
            'quiet' => $quietHours 
        ]);
    }
    // 5. LOGOUT
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Sesión cerrada']);
    }
}