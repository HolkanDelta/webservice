
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
import { type Client } from '@/types';
import { Button, buttonVariants } from '@/components/ui/button';


export default function Index({  clients }: { clients: Client[] } ) {
    const deleteClient = (id: number) =>{
        if (confirm('Estas Completamente Seguro')) {
            router.delete(route('clientes.destroy', { id }));            
        }
    }
    return (
        <AppLayout>
            <Head title="Clients - List" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <Link className={buttonVariants({ variant: 'outline'})} href={'/clientes/create'}> Agregar Cliente </Link>
                <Table className='mt-4'>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Nombre</TableHead>
                            <TableHead>UserAPI</TableHead>
                            <TableHead>KeyApi</TableHead>
                            <TableHead>Acciones</TableHead>                            
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        {clients.map((client) => (
                            <TableRow key = {client.id}>
                                <TableCell>{client.name}</TableCell>
                                <TableCell>{client.user_name}</TableCell>
                                <TableCell>{client.api_key}</TableCell>
                                <TableCell>
                                    <Link className={buttonVariants({ variant: 'outline'})} href={route('clientes.edit', { cliente: client.id })}> 
                                        Editar 
                                    </Link>
                                    <Button variant={'destructive'} onClick={() => deleteClient(client.id)}>
                                        Eliminar
                                    </Button>
                                </TableCell>
                            </TableRow>
                        ))}
                    </TableBody>
                </Table>
            </div>
        </AppLayout>
    );
}
