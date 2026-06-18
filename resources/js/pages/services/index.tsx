import AppLayout from '@/layouts/app-layout';
import { Head, router, Link } from '@inertiajs/react';
import {
    TableBody,
    Table,
    TableHead,
    TableRow,
    TableCell,
    TableHeader
} from '@/components/ui/table';
import { type Service } from '@/types';
import { Button, buttonVariants } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Plus, Search, Pencil, Trash2, Server, Users, Activity } from 'lucide-react';
import { useState } from 'react';
import { Input } from '@/components/ui/input';

export default function Index({ services }: { services: Service[] }) {
    const [search, setSearch] = useState('');

    const deleteService = (id: number) => {
        if (confirm('¿Estás completamente seguro de eliminar este servicio?')) {
            router.delete(route('servicios.destroy', { servicio: id }));
        }
    }

    const filteredServices = services.filter((service) =>
        service.name.toLowerCase().includes(search.toLowerCase()) ||
        service.description.toLowerCase().includes(search.toLowerCase()) ||
        service.base_url.toLowerCase().includes(search.toLowerCase())
    );

    // Calculate stats
    const totalServices = services.length;
    const totalConnections = services.reduce((acc, service) => acc + (service.clients?.length || 0), 0);

    return (
        <AppLayout>
            <Head title="Servicios - Listado" />
            <div className="flex h-full flex-1 flex-col gap-6 p-6">
                
                {/* Header Section */}
                <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight text-zinc-900 dark:text-zinc-50">Servicios</h1>
                        <p className="text-sm text-zinc-500 dark:text-zinc-400 mt-1">
                            Administra los servicios web SOAP/REST disponibles y los clientes vinculados a cada uno.
                        </p>
                    </div>
                    <Link href={route('servicios.create')} className={buttonVariants({ variant: 'default' })}>
                        <Plus className="mr-2 h-4 w-4" /> Agregar Servicio
                    </Link>
                </div>

                {/* Stats Section */}
                <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div className="flex items-center gap-4 rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-950">
                        <div className="rounded-lg bg-orange-100 p-3 text-orange-600 dark:bg-orange-950/50 dark:text-orange-400">
                            <Server className="h-6 w-6" />
                        </div>
                        <div>
                            <p className="text-sm font-medium text-zinc-500 dark:text-zinc-400">Total Servicios</p>
                            <h3 className="text-2xl font-bold text-zinc-900 dark:text-zinc-50">{totalServices}</h3>
                        </div>
                    </div>
                    <div className="flex items-center gap-4 rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-950">
                        <div className="rounded-lg bg-orange-100 p-3 text-orange-600 dark:bg-orange-950/50 dark:text-orange-400">
                            <Users className="h-6 w-6" />
                        </div>
                        <div>
                            <p className="text-sm font-medium text-zinc-500 dark:text-zinc-400">Clientes Vinculados</p>
                            <h3 className="text-2xl font-bold text-zinc-900 dark:text-zinc-50">{totalConnections}</h3>
                        </div>
                    </div>
                    <div className="flex items-center gap-4 rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-950">
                        <div className="rounded-lg bg-orange-100 p-3 text-orange-600 dark:bg-orange-950/50 dark:text-orange-400">
                            <Activity className="h-6 w-6" />
                        </div>
                        <div>
                            <p className="text-sm font-medium text-zinc-500 dark:text-zinc-400">Estado Monitoreo</p>
                            <h3 className="text-2xl font-bold text-green-600 dark:text-green-400">En Línea</h3>
                        </div>
                    </div>
                </div>

                {/* Main Content Card */}
                <div className="flex flex-col rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-950 overflow-hidden">
                    
                    {/* Filter Bar */}
                    <div className="flex items-center gap-2 border-b border-zinc-200 p-4 dark:border-zinc-800">
                        <div className="relative flex-1 max-w-sm">
                            <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-zinc-400 dark:text-zinc-500" />
                            <Input
                                placeholder="Buscar servicio por nombre, descripción o URL..."
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                className="pl-9 w-full"
                            />
                        </div>
                    </div>

                    {/* Table Section */}
                    <div className="overflow-x-auto">
                        <Table>
                            <TableHeader className="bg-zinc-50 dark:bg-zinc-900/50">
                                <TableRow>
                                    <TableHead className="font-semibold text-zinc-900 dark:text-zinc-50">Nombre</TableHead>
                                    <TableHead className="font-semibold text-zinc-900 dark:text-zinc-50">Descripción</TableHead>
                                    <TableHead className="font-semibold text-zinc-900 dark:text-zinc-50">Base URL / Endpoint</TableHead>
                                    <TableHead className="font-semibold text-zinc-900 dark:text-zinc-50">Clientes Activos</TableHead>
                                    <TableHead className="text-right font-semibold text-zinc-900 dark:text-zinc-50">Acciones</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {filteredServices.length > 0 ? (
                                    filteredServices.map((service) => (
                                        <TableRow key={service.id} className="hover:bg-zinc-50/50 dark:hover:bg-zinc-900/20 transition-colors">
                                            <TableCell className="font-medium text-zinc-900 dark:text-zinc-50 py-4">
                                                <Link
                                                    href={route('servicios.show', { servicio: service.id })}
                                                    className="hover:text-orange-500 hover:underline transition-colors"
                                                >
                                                    {service.name}
                                                </Link>
                                            </TableCell>
                                            <TableCell className="text-zinc-600 dark:text-zinc-300 max-w-[250px] truncate" title={service.description}>
                                                {service.description}
                                            </TableCell>
                                            <TableCell className="text-zinc-600 dark:text-zinc-300">
                                                <code className="rounded bg-zinc-100 px-1.5 py-0.5 text-xs font-mono dark:bg-zinc-800 max-w-[300px] truncate block" title={service.base_url}>
                                                    {service.base_url}
                                                </code>
                                            </TableCell>
                                            <TableCell>
                                                <div className="flex flex-wrap gap-1.5">
                                                    {service.clients && service.clients.length > 0 ? (
                                                        service.clients.map((client) => (
                                                            <Badge key={client.id} variant="secondary" className="px-2 py-0.5 text-[11px] font-normal">
                                                                {client.name}
                                                            </Badge>
                                                        ))
                                                    ) : (
                                                        <span className="text-xs text-zinc-400 dark:text-zinc-500 italic">Sin clientes</span>
                                                    )}
                                                </div>
                                            </TableCell>
                                            <TableCell className="text-right">
                                                <div className="flex items-center justify-end gap-2">
                                                    <Link 
                                                        className={buttonVariants({ variant: 'outline', size: 'sm' })} 
                                                        href={route('servicios.edit', { servicio: service.id })}
                                                    >
                                                        <Pencil className="mr-1.5 h-3.5 w-3.5" /> Editar
                                                    </Link>
                                                    <Button 
                                                        variant="destructive" 
                                                        size="sm" 
                                                        onClick={() => deleteService(service.id)}
                                                    >
                                                        <Trash2 className="mr-1.5 h-3.5 w-3.5" /> Eliminar
                                                    </Button>
                                                </div>
                                            </TableCell>
                                        </TableRow>
                                    ))
                                ) : (
                                    <TableRow>
                                        <TableCell colSpan={5} className="text-center py-12 text-zinc-400 dark:text-zinc-500">
                                            No se encontraron servicios que coincidan con la búsqueda.
                                        </TableCell>
                                    </TableRow>
                                )}
                            </TableBody>
                        </Table>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
