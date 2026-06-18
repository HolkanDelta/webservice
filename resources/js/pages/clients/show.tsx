import AppLayout from '@/layouts/app-layout';
import { Head, Link } from '@inertiajs/react';
import { type Client } from '@/types';
import { buttonVariants } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { ArrowLeft, Pencil, User, Key, Shield, Server, FileText } from 'lucide-react';

interface ClientType {
    id: number;
    name: string;
    user_name: string;
    user_pass: string;
    apikey: string;
    token?: string;
    company_id?: string;
    services?: { id: number; name: string; description: string }[];
}

export default function ShowClient({ cliente }: { cliente: ClientType }) {
    return (
        <AppLayout>
            <Head title={`Clientes - ${cliente.name}`} />
            <div className="flex h-full flex-1 flex-col gap-6 p-6">
                
                {/* Navigation and Actions Header */}
                <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div className="flex items-center gap-3">
                        <Link href={route('clientes.index')} className={buttonVariants({ variant: 'ghost', size: 'icon' })}>
                            <ArrowLeft className="h-4 w-4" />
                        </Link>
                        <div>
                            <h1 className="text-3xl font-bold tracking-tight text-zinc-900 dark:text-zinc-50">{cliente.name}</h1>
                            <p className="text-sm text-zinc-500 dark:text-zinc-400 mt-0.5">Detalles del cliente e integraciones activas</p>
                        </div>
                    </div>
                    <Link href={route('clientes.edit', { cliente: cliente.id })} className={buttonVariants({ variant: 'default' })}>
                        <Pencil className="mr-2 h-4 w-4" /> Editar Cliente
                    </Link>
                </div>

                {/* Content Grid */}
                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    
                    {/* Left Column: API & Auth details */}
                    <div className="lg:col-span-2 flex flex-col gap-6">
                        
                        {/* API Config Card */}
                        <div className="rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-950 p-6">
                            <h3 className="text-lg font-bold text-zinc-900 dark:text-zinc-50 flex items-center gap-2 mb-4 border-b border-zinc-100 pb-3 dark:border-zinc-900">
                                <Key className="h-5 w-5 text-orange-500" /> Configuración de API & Accesos
                            </h3>
                            
                            <div className="flex flex-col gap-4">
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div className="rounded-lg bg-zinc-50 p-4 dark:bg-zinc-900/50">
                                        <p className="text-xs font-semibold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">Usuario API</p>
                                        <code className="text-sm font-mono text-zinc-900 dark:text-zinc-100 mt-1 block select-all">
                                            {cliente.user_name}
                                        </code>
                                    </div>
                                    <div className="rounded-lg bg-zinc-50 p-4 dark:bg-zinc-900/50">
                                        <p className="text-xs font-semibold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">Contraseña de Usuario</p>
                                        <code className="text-sm font-mono text-zinc-900 dark:text-zinc-100 mt-1 block select-all">
                                            {cliente.user_pass || '—'}
                                        </code>
                                    </div>
                                </div>

                                <div className="rounded-lg bg-zinc-50 p-4 dark:bg-zinc-900/50">
                                    <p className="text-xs font-semibold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">API Key / Token General</p>
                                    <code className="text-sm font-mono text-zinc-900 dark:text-zinc-100 mt-1 block break-all select-all">
                                        {cliente.apikey}
                                    </code>
                                </div>

                                {cliente.company_id && (
                                    <div className="rounded-lg bg-zinc-50 p-4 dark:bg-zinc-900/50">
                                        <p className="text-xs font-semibold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">Company ID (Servicios WSDL)</p>
                                        <code className="text-sm font-mono text-zinc-900 dark:text-zinc-100 mt-1 block select-all">
                                            {cliente.company_id}
                                        </code>
                                    </div>
                                )}
                            </div>
                        </div>

                        {/* Token Card */}
                        <div className="rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-950 p-6">
                            <h3 className="text-lg font-bold text-zinc-900 dark:text-zinc-50 flex items-center gap-2 mb-4 border-b border-zinc-100 pb-3 dark:border-zinc-900">
                                <Shield className="h-5 w-5 text-orange-500" /> Token de Transmisión Activo
                            </h3>
                            {cliente.token ? (
                                <div className="flex flex-col gap-3">
                                    <p className="text-xs text-zinc-500 dark:text-zinc-400">
                                        Este token se actualiza automáticamente durante la autenticación con los servicios SOAP correspondientes.
                                    </p>
                                    <div className="relative rounded-lg bg-zinc-950 p-4 font-mono text-xs text-zinc-100 dark:bg-black max-h-[180px] overflow-y-auto break-all border border-zinc-800 select-all">
                                        {cliente.token}
                                    </div>
                                </div>
                            ) : (
                                <p className="text-sm text-zinc-500 dark:text-zinc-400 italic">
                                    No se ha registrado ningún token activo para este cliente.
                                </p>
                            )}
                        </div>
                    </div>

                    {/* Right Column: Services list */}
                    <div className="flex flex-col gap-6">
                        <div className="rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-950 p-6">
                            <h3 className="text-lg font-bold text-zinc-900 dark:text-zinc-50 flex items-center gap-2 mb-4 border-b border-zinc-100 pb-3 dark:border-zinc-900">
                                <Server className="h-5 w-5 text-orange-500" /> Servicios Habilitados
                            </h3>
                            <p className="text-xs text-zinc-500 dark:text-zinc-400 mb-4">
                                Servicios SDK a los que este cliente transmite información de telemetría de forma programada.
                            </p>
                            <div className="flex flex-col gap-3">
                                {cliente.services && cliente.services.length > 0 ? (
                                    cliente.services.map((service) => (
                                        <div 
                                            key={service.id} 
                                            className="flex items-center justify-between p-3 rounded-lg border border-zinc-100 dark:border-zinc-900 bg-zinc-50/50 dark:bg-zinc-900/20"
                                        >
                                            <div className="flex flex-col">
                                                <span className="text-sm font-semibold text-zinc-900 dark:text-zinc-100">
                                                    {service.description || service.name}
                                                </span>
                                                <span className="text-xs text-zinc-400 mt-0.5">
                                                    {service.name}
                                                </span>
                                            </div>
                                            <Badge variant="outline" className="border-green-200 bg-green-50 text-green-700 dark:border-green-900 dark:bg-green-950/30 dark:text-green-400">
                                                Activo
                                            </Badge>
                                        </div>
                                    ))
                                ) : (
                                    <div className="text-center py-6 border border-dashed border-zinc-200 dark:border-zinc-800 rounded-lg">
                                        <p className="text-sm text-zinc-400 dark:text-zinc-500 italic">Sin servicios asignados</p>
                                    </div>
                                )}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
