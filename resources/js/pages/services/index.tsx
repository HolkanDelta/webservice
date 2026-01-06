
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


export default function Index({  services }: { services: Service[] } ) {
    const deleteClient = (id: number) =>{
        if (confirm('Estas Completamente Seguro')) {
            router.delete(route('servicios.destroy', { id }));            
        }
    }
    return (
        <AppLayout>
            <Head title="Clients - List" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <Link className={buttonVariants({ variant: 'outline'})} href={'/servicios/create'}> Agregar Servicio </Link>
                <Table className='mt-4'>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Nombre</TableHead>
                            <TableHead>Descripcion</TableHead>
                            <TableHead>BaseUrl</TableHead>
                            <TableHead>Acciones</TableHead>                            
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        {services.map((service) => (
                            <TableRow key = {service.id}>
                                <TableCell>{service.name}</TableCell>
                                <TableCell>{service.description}</TableCell>
                                <TableCell>{service.base_url}</TableCell>
                                <TableCell>
                                    <Link className={buttonVariants({ variant: 'outline'})} href={route('servicios.edit', { servicio: service.id })}> 
                                        Editar 
                                    </Link>
                                    <Button variant={'destructive'} onClick={() => deleteClient(service.id)}>
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
