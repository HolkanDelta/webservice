import AppLayout from '@/layouts/app-layout';
import { Head, router, Link, useForm } from '@inertiajs/react';
import { Button, buttonVariants } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { FormEventHandler, useRef } from 'react';
interface ClientType  {
    id: number;
    name: string;
    user_name: string;
    user_pass: string;
    api_key: string;
};


export default function EditClient({cliente} : { cliente: ClientType[] } ) {
    console.log(cliente);
    
    const taskEdditClient = useRef<HTMLFormElement>(null);
    const { data, setData, put, processing, errors, reset } = useForm<ClientType>({
        id: cliente.id,
        name: cliente.name,
        user_name: cliente.user_name,
        user_pass: cliente.user_pass,
        api_key: cliente.api_key
    });
    const EditClientTask : FormEventHandler = (e) => {
        e.preventDefault();
        put(route('clientes.update', data.id), {
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
            <Head title="Clients - Edit" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-bold">Clientes - Editar</h1>
                    <Link href={route('clientes.index')} className={buttonVariants({ variant: 'secondary' })}>
                        Lista de Clientes
                    </Link>
                </div>
                <form onSubmit={ EditClientTask }  className="flex flex-col gap-4">
                    <div>
                        <Label htmlFor="name">Nombre</Label>
                        <Input
                            id="name"
                            ref={taskEdditClient}
                            value={data.name}
                            onChange={(e) => setData('name', e.target.value)}   
                            className="mt-1 w-full"
                            required
                        />
                        {errors.name && <p className="mt-1 text-sm text-red-600">{errors.name}</p>}
                    </div>
                    <div>
                        <Label htmlFor="user_name">User Name</Label>
                        <Input
                            id="user_name"
                            ref={taskEdditClient}
                            value={data.user_name}
                            onChange={(e) => setData('user_name', e.target.value)}
                            className="mt-1 w-full"
                            required
                        />
                        {errors.user_name && <p className="mt-1 text-sm text-red-600">{errors.user_name}</p>}
                    </div>
                    <div>
                        <Label htmlFor="user_pass">User Password</Label>
                        <Input
                            id="user_pass"
                            ref={taskEdditClient}
                            type="password"
                            value={data.user_pass}
                            onChange={(e) => setData('user_pass', e.target.value)}
                            className="mt-1 w-full"
                            required
                        />
                        {errors.user_pass && <p className="mt-1 text-sm text-red-600">{errors.user_pass}</p>}
                    </div>
                    <div>
                        <Label htmlFor="api_key">API Key</Label>
                        <Input
                            id="api_key"
                            ref={taskEdditClient}
                            value={data.api_key}
                            onChange={(e) => setData('api_key', e.target.value)}
                            className="mt-1 w-full"
                            required
                        />
                        {errors.api_key && <p className="mt-1 text-sm text-red-600">{errors.api_key}</p>}
                    </div>
                    <Button variant={'secondary'} type='submit'>Actualizar</Button>
                </form>
            </div>
        </AppLayout>
    );
}