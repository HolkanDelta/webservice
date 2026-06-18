import AppLayout from '@/layouts/app-layout';
import { Head, Link, useForm } from '@inertiajs/react';
import { type Service } from '@/types';
import { Button, buttonVariants } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { FormEventHandler, useRef } from 'react';
import { ArrowLeft, UserPlus, ShieldAlert } from 'lucide-react';

type CreateClient = {
    name: string;
    user_name: string;
    user_pass: string;
    apikey: string;
    services: number[];
};

export default function CreateClient({ services }: { services: Service[] }) {
    const taskCreateClientForm = useRef<HTMLFormElement>(null);
    const { data, setData, post, processing, errors, reset } = useForm<CreateClient>({
        name: '',
        user_name: '',
        user_pass: '',
        apikey: '',
        services: [],
    });

    const CreateClientTask: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('clientes.store'), {
            onSuccess: () => {
                reset();
            },
            onError: () => {
                if (taskCreateClientForm.current) {
                    const firstErrorElement = taskCreateClientForm.current.querySelector<HTMLElement>('.text-red-600');
                    firstErrorElement?.focus();
                }
            },
        });
    };

    return (
        <AppLayout>
            <Head title="Clientes - Crear Nuevo" />
            <div className="flex h-full flex-1 flex-col gap-6 p-6 max-w-4xl mx-auto w-full">
                
                {/* Header */}
                <div className="flex items-center gap-3">
                    <Link href={route('clientes.index')} className={buttonVariants({ variant: 'ghost', size: 'icon' })}>
                        <ArrowLeft className="h-4 w-4" />
                    </Link>
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight text-zinc-900 dark:text-zinc-50">Nuevo Cliente</h1>
                        <p className="text-sm text-zinc-500 dark:text-zinc-400 mt-0.5">Crea un perfil de cliente con sus credenciales y accesos</p>
                    </div>
                </div>

                {/* Form Card */}
                <div className="rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-950 p-6">
                    <form ref={taskCreateClientForm} onSubmit={CreateClientTask} className="flex flex-col gap-6">
                        
                        <div className="flex items-center gap-2 text-zinc-800 dark:text-zinc-200 border-b border-zinc-100 pb-3 dark:border-zinc-900 mb-2">
                            <UserPlus className="h-5 w-5 text-orange-500" />
                            <span className="font-bold text-base">Datos Generales y de Autenticación</span>
                        </div>

                        {/* Input Grid */}
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div className="flex flex-col gap-1.5">
                                <Label htmlFor="name">Nombre del Cliente / Empresa</Label>
                                <Input
                                    id="name"
                                    value={data.name}
                                    onChange={(e) => setData('name', e.target.value)}
                                    placeholder="Ej. Transportes Pichardo"
                                    required
                                />
                                {errors.name && <p className="text-xs text-red-600 font-medium">{errors.name}</p>}
                            </div>

                            <div className="flex flex-col gap-1.5">
                                <Label htmlFor="user_name">Usuario de API</Label>
                                <Input
                                    id="user_name"
                                    value={data.user_name}
                                    onChange={(e) => setData('user_name', e.target.value)}
                                    placeholder="Usuario para autenticación"
                                    required
                                />
                                {errors.user_name && <p className="text-xs text-red-600 font-medium">{errors.user_name}</p>}
                            </div>

                            <div className="flex flex-col gap-1.5">
                                <Label htmlFor="user_pass">Contraseña de API</Label>
                                <Input
                                    id="user_pass"
                                    type="password"
                                    value={data.user_pass}
                                    onChange={(e) => setData('user_pass', e.target.value)}
                                    placeholder="Contraseña o frase secreta"
                                    required
                                />
                                {errors.user_pass && <p className="text-xs text-red-600 font-medium">{errors.user_pass}</p>}
                            </div>

                            <div className="flex flex-col gap-1.5">
                                <Label htmlFor="apikey">API Key / Token General</Label>
                                <Input
                                    id="apikey"
                                    value={data.apikey}
                                    onChange={(e) => setData('apikey', e.target.value)}
                                    placeholder="Token de acceso (Mapon u otros)"
                                    required
                                />
                                {errors.apikey && <p className="text-xs text-red-600 font-medium">{errors.apikey}</p>}
                            </div>
                        </div>

                        {/* Services checkboxes */}
                        <div className="flex flex-col gap-3 mt-2">
                            <div className="border-b border-zinc-100 pb-2 dark:border-zinc-900">
                                <Label className="text-base font-bold text-zinc-900 dark:text-zinc-50">Asignación de Servicios</Label>
                                <p className="text-xs text-zinc-500 mt-0.5">Selecciona a qué servicios SDK transmitirá datos este cliente</p>
                            </div>
                            
                            {services.length > 0 ? (
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-3 rounded-lg border border-zinc-200 p-4 dark:border-zinc-800 bg-zinc-50/20 dark:bg-zinc-900/10">
                                    {services.map((service) => (
                                        <label 
                                            key={service.id} 
                                            htmlFor={`service-${service.id}`}
                                            className="flex items-center space-x-3 py-2 px-3 rounded-md hover:bg-zinc-50 dark:hover:bg-zinc-900 cursor-pointer select-none transition-colors border border-transparent hover:border-zinc-100 dark:hover:border-zinc-800"
                                        >
                                            <input
                                                type="checkbox"
                                                id={`service-${service.id}`}
                                                checked={data.services.includes(service.id)}
                                                onChange={(e) => {
                                                    if (e.target.checked) {
                                                        setData('services', [...data.services, service.id]);
                                                    } else {
                                                        setData('services', data.services.filter((id) => id !== service.id));
                                                    }
                                                }}
                                                className="h-4.5 w-4.5 rounded border-zinc-300 text-orange-500 focus:ring-orange-500 cursor-pointer accent-orange-500"
                                            />
                                            <div className="flex flex-col">
                                                <span className="text-sm font-semibold text-zinc-800 dark:text-zinc-200">
                                                    {service.description || service.name}
                                                </span>
                                                <span className="text-[11px] text-zinc-400">
                                                    {service.name}
                                                </span>
                                            </div>
                                        </label>
                                    ))}
                                </div>
                            ) : (
                                <div className="flex items-center gap-2 p-4 rounded-lg bg-orange-50 border border-orange-200 text-orange-800 dark:bg-orange-950/20 dark:border-orange-900 dark:text-orange-400">
                                    <ShieldAlert className="h-5 w-5" />
                                    <p className="text-sm">
                                        No hay servicios configurados en el sistema. Primero crea un servicio para poder asignarlo.
                                    </p>
                                </div>
                            )}
                            {errors.services && <p className="text-xs text-red-600 font-medium">{errors.services}</p>}
                        </div>

                        {/* Submit Actions */}
                        <div className="flex items-center justify-end gap-3 border-t border-zinc-100 pt-4 dark:border-zinc-900 mt-2">
                            <Link href={route('clientes.index')} className={buttonVariants({ variant: 'outline' })}>
                                Cancelar
                            </Link>
                            <Button type="submit" disabled={processing} className={buttonVariants({ variant: 'default' })}>
                                Crear Cliente
                            </Button>
                        </div>
                    </form>
                </div>
            </div>
        </AppLayout>
    );
}