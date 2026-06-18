<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Artisan;

class DeployCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:deploy';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Despliega los cambios de forma segura en producción (Migraciones incrementales, npm y caché)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Iniciando despliegue de cambios en producción...');

        // 1. Ejecutar migraciones de forma segura
        $this->info('⚙️ Ejecutando migraciones de base de datos de forma segura (incremental)...');
        try {
            $exitCode = Artisan::call('migrate', [
                '--force' => true,
            ]);
            $output = Artisan::output();
            $this->line($output);
            if ($exitCode !== 0) {
                $this->error('❌ Error ejecutando las migraciones.');
                return 1;
            }
            $this->info('✅ Migraciones completadas.');
        } catch (\Exception $e) {
            $this->error('❌ Error en las migraciones: ' . $e->getMessage());
            return 1;
        }

        // 2. Instalar dependencias npm (jspdf, etc.)
        $this->info('📦 Instalando dependencias de Node (npm install)...');
        
        $phpDir = dirname(PHP_BINARY);
        $env = ['PATH' => $phpDir . PATH_SEPARATOR . (getenv('PATH') ?: $_SERVER['PATH'] ?: '')];
        
        $command = PHP_OS_FAMILY === 'Windows' ? 'cmd /c npm install' : 'npm install';
        $npmInstall = Process::env($env)->run($command);
        if ($npmInstall->failed()) {
            $this->error('❌ Error al ejecutar npm install:');
            $this->line($npmInstall->errorOutput());
            return 1;
        }
        $this->info('✅ Dependencias de Node instaladas.');

        // 3. Compilar assets de Vite para producción
        $this->info('⚡ Compilando assets con Vite (npm run build)...');
        $command = PHP_OS_FAMILY === 'Windows' ? 'cmd /c npm run build' : 'npm run build';
        $npmBuild = Process::env($env)->run($command);
        if ($npmBuild->failed()) {
            $this->error('❌ Error al compilar assets (npm run build):');
            $this->line($npmBuild->errorOutput());
            return 1;
        }
        $this->line($npmBuild->output());
        $this->info('✅ Assets compilados correctamente.');

        // 4. Optimizar y limpiar cache de Laravel
        $this->info('🧹 Optimizando caché de Laravel (configuración, rutas y vistas)...');
        Artisan::call('optimize');
        $this->line(Artisan::output());

        // 5. Reiniciar trabajadores de cola (queue worker)
        $this->info('🔄 Reiniciando trabajadores de cola (queue worker)...');
        Artisan::call('queue:restart');
        $this->line(Artisan::output());

        $this->info('🎉 Despliegue completado con éxito de forma segura.');
        return 0;
    }
}
