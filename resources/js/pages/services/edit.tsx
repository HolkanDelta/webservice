import AppLayout from '@/layouts/app-layout';
import { Head, router, Link, useForm } from '@inertiajs/react';

import { Button, buttonVariants } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { FormEventHandler, useRef } from 'react';
interface ServiceType  {
    id: number;
    name: string;
    description: string;
    base_url: string;
    recurrence: string;
};


export default function ServiceEdit({  servicio }: { servicio: ServiceType[] } ) {
    console.log(servicio);
    
    const taskEdditClient = useRef<HTMLFormElement>(null);
    const { data, setData, put, processing, errors, reset } = useForm<ServiceType>({
        id: servicio.id,
        name: servicio.name,
        description: servicio.description,
        base_url: servicio.base_url,
        recurrence: servicio.recurrence,
    });
    const ServiceEditTask : FormEventHandler = (e) => {
        e.preventDefault();
        put(route('servicios.update', data.id), {
            onSuccess: () => {
                reset();
            },
            onError: () => {
                if (taskEdditClient.current) {
                    const firstErrorElement = taskEdditClient.current.querySelector<HTMLElement>('.text-red-600');
                    firstErrorElement?.focus();
                }
            },
        });
    };
    
    return (
            <AppLayout>
                <Head title="Services - Edit" />
                <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                    <div className="flex items-center justify-between">
                        <h1 className="text-2xl font-bold">Service - Editar</h1>
                        <Link href={route('servicios.index')} className={buttonVariants({ variant: 'secondary' })}>
                            Lista de Servicios
                        </Link>
                    </div>
                    <form onSubmit={ ServiceEditTask }  className="flex flex-col gap-4">
                        <div>
                            <Label htmlFor="name">Nombre</Label>
                            <Input
                                id="name"
                                value={data.name}
                                onChange={(e) => setData('name', e.target.value)}   
                                className="mt-1 w-full"
                                required
                            />
                            {errors.name && <p className="mt-1 text-sm text-red-600">{errors.name}</p>}
                        </div>
                        <div>
                            <Label htmlFor="description">Descripcion</Label>
                            <Input
                                id="description"
                                value={data.description}
                                onChange={(e) => setData('description', e.target.value)}
                                className="mt-1 w-full"
                                required
                            />
                            {errors.description && <p className="mt-1 text-sm text-red-600">{errors.description}</p>}
                        </div>
                        <div>
                            <Label htmlFor="base_url">Base URL</Label>
                            <Input
                                id="base_url"
                                value={data.base_url}
                                onChange={(e) => setData('base_url', e.target.value)}
                                className="mt-1 w-full"
                                required
                            />
                            {errors.base_url && <p className="mt-1 text-sm text-red-600">{errors.base_url}</p>}
                        </div>
                        <div>
                            <Label htmlFor="recurrence">Recurrencia</Label>
                            <Input
                                id="recurrence"
                                value={data.recurrence}
                                onChange={(e) => setData('recurrence', e.target.value)}
                                className="mt-1 w-full"
                                required
                            />
                            {errors.recurrence && <p className="mt-1 text-sm text-red-600">{errors.recurrence}</p>}
                        </div>
                        <Button variant={'secondary'} type='submit'>Actializar</Button>
                    </form>
                </div>
            </AppLayout>
        );
}