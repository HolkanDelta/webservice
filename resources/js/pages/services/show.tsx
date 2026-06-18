import AppLayout from '@/layouts/app-layout';
import { Head, Link } from '@inertiajs/react';
import { buttonVariants } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { ArrowLeft, Pencil, Server, Info, Link as LinkIcon, Users, Clock } from 'lucide-react';

interface ServiceType {
    id: number;
    name: string;
    description: string;
    base_url: string;
    recurrence: string;
    clients?: { id: number; name: string; user_name: string; apikey: string }[];
}

export default function ShowService({ servicio }: { servicio: ServiceType }) {
    return (
        <AppLayout>
            <Head title={`Servicios - ${servicio.description || servicio.name}`} />
            <div className="flex h-full flex-1 flex-col gap-6 p-6">
                
                {/* Navigation and Actions Header */}
                <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div className="flex items-center gap-3">
                        <Link href={route('servicios.index')} className={buttonVariants({ variant: 'ghost', size: 'icon' })}>
                            <ArrowLeft className="h-4 w-4" />
                        </Link>
                        <div>
                            <h1 className="text-3xl font-bold tracking-tight text-zinc-900 dark:text-zinc-50">
                                {servicio.description || servicio.name}
                            </h1>
                            <p className="text-sm text-zinc-500 dark:text-zinc-400 mt-0.5">Detalles del servicio de integración</p>
                        </div>
                    </div>
                    <Link href={route('servicios.edit', { servicio: servicio.id })} className={buttonVariants({ variant: 'default' })}>
                        <Pencil className="mr-2 h-4 w-4" /> Editar Servicio
                    </Link>
                </div>

                {/* Content Grid */}
                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    
                    {/* Left Column: Service Config */}
                    <div className="lg:col-span-2 flex flex-col gap-6">
                        
                        {/* Service configuration details */}
                        <div className="rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-950 p-6">
                            <h3 className="text-lg font-bold text-zinc-900 dark:text-zinc-50 flex items-center gap-2 mb-4 border-b border-zinc-100 pb-3 dark:border-zinc-900">
                                <Server className="h-5 w-5 text-orange-500" /> Configuración de Servicio
                            </h3>
                            
                            <div className="flex flex-col gap-4">
                                <div className="rounded-lg bg-zinc-50 p-4 dark:bg-zinc-900/50">
                                    <p className="text-xs font-semibold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">Nombre Identificador</p>
                                    <span className="text-sm font-semibold text-zinc-900 dark:text-zinc-100 mt-1 block">
                                        {servicio.name}
                                    </span>
                                </div>

                                <div className="rounded-lg bg-zinc-50 p-4 dark:bg-zinc-900/50">
                                    <p className="text-xs font-semibold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">Descripción del Servicio</p>
                                    <span className="text-sm text-zinc-700 dark:text-zinc-300 mt-1 block">
                                        {servicio.description || 'Sin descripción'}
                                    </span>
                                </div>

                                <div className="rounded-lg bg-zinc-50 p-4 dark:bg-zinc-900/50">
                                    <p className="text-xs font-semibold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">Base URL / Endpoint WSDL</p>
                                    {servicio.base_url ? (
                                        <code className="text-sm font-mono text-zinc-900 dark:text-zinc-100 mt-1 block break-all select-all">
                                            {servicio.base_url}
                                        </code>
                                    ) : (
                                        <span className="text-sm text-zinc-400 dark:text-zinc-500 italic block mt-1">Sin URL asignada</span>
                                    )}
                                </div>

                                <div className="flex items-center gap-3 rounded-lg bg-zinc-50 p-4 dark:bg-zinc-900/50">
                                    <Clock className="h-5 w-5 text-zinc-400" />
                                    <div>
                                        <p className="text-xs font-semibold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">Recurrencia (Planificación)</p>
                                        <span className="text-sm font-medium text-zinc-800 dark:text-zinc-200 block mt-0.5">
                                            {servicio.recurrence || 'Activa (Cada 5 minutos)'}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Right Column: Assigned Clients list */}
                    <div className="flex flex-col gap-6">
                        <div className="rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-950 p-6">
                            <h3 className="text-lg font-bold text-zinc-900 dark:text-zinc-50 flex items-center gap-2 mb-4 border-b border-zinc-100 pb-3 dark:border-zinc-900">
                                <Users className="h-5 w-5 text-orange-500" /> Clientes Suscritos
                            </h3>
                            <p className="text-xs text-zinc-500 dark:text-zinc-400 mb-4">
                                Clientes cuyas unidades de GPS y telemetría están transmitiéndose a este destino.
                            </p>
                            <div className="flex flex-col gap-3">
                                {servicio.clients && servicio.clients.length > 0 ? (
                                    servicio.clients.map((client) => (
                                        <div 
                                            key={client.id} 
                                            className="flex flex-col p-3 rounded-lg border border-zinc-100 dark:border-zinc-900 bg-zinc-50/50 dark:bg-zinc-900/20 gap-1"
                                        >
                                            <div className="flex items-center justify-between">
                                                <Link 
                                                    href={route('clientes.show', { cliente: client.id })} 
                                                    className="text-sm font-semibold text-zinc-900 dark:text-zinc-100 hover:text-orange-500 dark:hover:text-orange-400 transition-colors"
                                                >
                                                    {client.name}
                                                </Link>
                                                <Badge variant="outline" className="border-green-200 bg-green-50 text-green-700 dark:border-green-900 dark:bg-green-950/30 dark:text-green-400 text-[10px]">
                                                    Enlazado
                                                </Badge>
                                            </div>
                                            <div className="flex items-center gap-1.5 mt-1">
                                                <span className="text-[10px] text-zinc-400 font-semibold uppercase">API User:</span>
                                                <code className="text-[10px] font-mono text-zinc-600 dark:text-zinc-400">{client.user_name}</code>
                                            </div>
                                        </div>
                                    ))
                                ) : (
                                    <div className="text-center py-6 border border-dashed border-zinc-200 dark:border-zinc-800 rounded-lg">
                                        <p className="text-sm text-zinc-400 dark:text-zinc-500 italic">No hay clientes enlazados</p>
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
