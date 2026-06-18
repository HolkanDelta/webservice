import AppLayout from '@/layouts/app-layout';
import { Head, Link, useForm } from '@inertiajs/react';
import { Button, buttonVariants } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { FormEventHandler, useRef } from 'react';
import { ArrowLeft, Server, Save } from 'lucide-react';

type ServiceCreate = {
    name: string;
    description: string;
    base_url: string;
    recurrence: string;
};

export default function CreateService() {
    const taskCreateForm = useRef<HTMLFormElement>(null);
    const { data, setData, post, processing, errors, reset } = useForm<ServiceCreate>({
        name: '',
        description: '',
        base_url: '',
        recurrence: '',
    });

    const CreateServiceTask: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('servicios.store'), {
            onSuccess: () => {
                reset();
            },
            onError: () => {
                if (taskCreateForm.current) {
                    const firstErrorElement = taskCreateForm.current.querySelector<HTMLElement>('.text-red-600');
                    firstErrorElement?.focus();
                }
            },
        });
    };

    return (
        <AppLayout>
            <Head title="Servicios - Crear Nuevo" />
            <div className="flex h-full flex-1 flex-col gap-6 p-6 max-w-4xl mx-auto w-full">
                
                {/* Header */}
                <div className="flex items-center gap-3">
                    <Link href={route('servicios.index')} className={buttonVariants({ variant: 'ghost', size: 'icon' })}>
                        <ArrowLeft className="h-4 w-4" />
                    </Link>
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight text-zinc-900 dark:text-zinc-50">Nuevo Servicio</h1>
                        <p className="text-sm text-zinc-500 dark:text-zinc-400 mt-0.5">Define un nuevo endpoint SOAP/REST para el sistema</p>
                    </div>
                </div>

                {/* Form Card */}
                <div className="rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-950 p-6">
                    <form ref={taskCreateForm} onSubmit={CreateServiceTask} className="flex flex-col gap-6">
                        
                        <div className="flex items-center gap-2 text-zinc-800 dark:text-zinc-200 border-b border-zinc-100 pb-3 dark:border-zinc-900 mb-2">
                            <Server className="h-5 w-5 text-orange-500" />
                            <span className="font-bold text-base">Datos del Servicio Web</span>
                        </div>

                        {/* Input Grid */}
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div className="flex flex-col gap-1.5 md:col-span-2">
                                <Label htmlFor="name">Nombre del Servicio</Label>
                                <Input
                                    id="name"
                                    value={data.name}
                                    onChange={(e) => setData('name', e.target.value)}
                                    placeholder="Ej. app:kronh-command"
                                    required
                                />
                                {errors.name && <p className="text-xs text-red-600 font-medium">{errors.name}</p>}
                            </div>

                            <div className="flex flex-col gap-1.5 md:col-span-2">
                                <Label htmlFor="description">Descripción</Label>
                                <Input
                                    id="description"
                                    value={data.description}
                                    onChange={(e) => setData('description', e.target.value)}
                                    placeholder="Ej. Sincronización de coordenadas con la plataforma Kronh"
                                    required
                                />
                                {errors.description && <p className="text-xs text-red-600 font-medium">{errors.description}</p>}
                            </div>

                            <div className="flex flex-col gap-1.5 md:col-span-2">
                                <Label htmlFor="base_url">Base URL / Endpoint WSDL</Label>
                                <Input
                                    id="base_url"
                                    value={data.base_url}
                                    onChange={(e) => setData('base_url', e.target.value)}
                                    placeholder="Ej. https://api.kronh.com/webservice.php?wsdl"
                                    required
                                />
                                {errors.base_url && <p className="text-xs text-red-600 font-medium">{errors.base_url}</p>}
                            </div>

                            <div className="flex flex-col gap-1.5 md:col-span-2">
                                <Label htmlFor="recurrence">Recurrencia (Frecuencia de ejecución)</Label>
                                <Input
                                    id="recurrence"
                                    value={data.recurrence}
                                    onChange={(e) => setData('recurrence', e.target.value)}
                                    placeholder="Ej. */5 * * * * (Cada 5 minutos)"
                                    required
                                />
                                {errors.recurrence && <p className="text-xs text-red-600 font-medium">{errors.recurrence}</p>}
                            </div>
                        </div>

                        {/* Actions */}
                        <div className="flex items-center justify-end gap-3 border-t border-zinc-100 pt-5 dark:border-zinc-900 mt-4">
                            <Link href={route('servicios.index')} className={buttonVariants({ variant: 'outline' })}>
                                Cancelar
                            </Link>
                            <Button type="submit" className="bg-orange-600 hover:bg-orange-500 text-white font-medium gap-2" disabled={processing}>
                                <Save className="h-4 w-4" />
                                {processing ? 'Creando...' : 'Crear Servicio'}
                            </Button>
                        </div>
                    </form>
                </div>
            </div>
        </AppLayout>
    );
}