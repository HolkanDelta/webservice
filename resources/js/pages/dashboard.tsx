import AppLayout from '@/layouts/app-layout';
import { Head, router } from '@inertiajs/react';
import { useState } from 'react';
import { jsPDF } from 'jspdf';
import { 
    Server, 
    Activity, 
    CheckCircle2, 
    Clock, 
    Database, 
    RefreshCw, 
    Terminal,
    ChevronDown,
    ChevronUp,
    FileJson,
    Download
} from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';

interface ServiceData {
    id: number;
    name: string;
    description: string;
    base_url: string;
    recurrence: string;
    clients_count: number;
    last_run: {
        status: 'success' | 'failure' | 'partial';
        message: string | null;
        created_at: string;
        payload: any;
    } | null;
    last_ok: string | null;
    reliability: number;
    avg_runtime: number;
}

interface RecentLog {
    id: number;
    service_name: string;
    status: 'success' | 'failure' | 'partial';
    message: string | null;
    runtime_ms: number;
    created_at: string;
}

interface DashboardProps {
    metrics: {
        total_services: number;
        operational_services: number;
        global_success_rate: number;
        global_avg_runtime: number;
    };
    services: ServiceData[];
    recent_logs: RecentLog[];
}

export default function Dashboard({ metrics, services, recent_logs }: DashboardProps) {
    const [selectedService, setSelectedService] = useState<ServiceData | null>(null);
    const [expandedClientIndex, setExpandedClientIndex] = useState<number | null>(null);

    const safeStringify = (val: any, pretty: boolean = false): string => {
        if (val === null || val === undefined) return '';
        if (typeof val === 'string') return val;
        try {
            return pretty ? JSON.stringify(val, null, 2) : JSON.stringify(val);
        } catch (e) {
            return String(val);
        }
    };

    const handleRefresh = () => {
        router.reload({ only: ['metrics', 'services', 'recent_logs'] });
    };

    function formatRelativeTime(dateString: string | null) {
        if (!dateString) return 'Nunca';
        const date = new Date(dateString);
        const now = new Date();
        const diffMs = now.getTime() - date.getTime();
        
        if (diffMs < 0) return 'Ahora';
        
        const diffMins = Math.round(diffMs / 60000);
        const diffHours = Math.round(diffMs / 3600000);

        if (diffMins < 1) return 'Hace unos segundos';
        if (diffMins < 60) return `Hace ${diffMins} min${diffMins > 1 ? 's' : ''}`;
        if (diffHours < 24) return `Hace ${diffHours} hora${diffHours > 1 ? 's' : ''}`;
        return date.toLocaleDateString('es-ES', { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' });
    }

    const getReliabilityColor = (reliability: number) => {
        if (reliability >= 95) return 'text-green-600 bg-green-50 dark:text-green-400 dark:bg-green-950/20';
        if (reliability >= 85) return 'text-orange-600 bg-orange-50 dark:text-orange-400 dark:bg-orange-950/20';
        return 'text-red-600 bg-red-50 dark:text-red-400 dark:bg-red-950/20';
    };

    const getReliabilityBarColor = (reliability: number) => {
        if (reliability >= 95) return 'bg-green-500';
        if (reliability >= 85) return 'bg-orange-500';
        return 'bg-red-500';
    };

    const exportToExcel = (service: ServiceData) => {
        if (!service.last_run || !service.last_run.payload) return;
        
        const timestamp = new Date(service.last_run.created_at).toLocaleString('es-ES');
        let csvContent = "data:text/csv;charset=utf-8,\uFEFF"; // Add BOM for UTF-8 Excel support
        csvContent += "Fecha/Hora,Cliente,Estado,Respuesta/Mensaje,Payload Enviado (GPS Data),Respuesta Servidor (SOAP/REST)\n";
        
        service.last_run.payload.forEach((row: any) => {
            const statusText = row.status === 'success' ? 'Correcto' : 'Error';
            const cleanMessage = (row.message || '').replace(/"/g, '""');
            const cleanPayload = safeStringify(row.transmission_payload).replace(/"/g, '""');
            const cleanResponse = safeStringify(row.transmission_response).replace(/"/g, '""');
            
            csvContent += `"${timestamp}","${row.client}","${statusText}","${cleanMessage}","${cleanPayload}","${cleanResponse}"\n`;
        });
        
        const encodedUri = encodeURI(csvContent);
        const link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", `interaccion_${service.name.replace(/\s+/g, '_')}.csv`);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    };

    const exportToPdf = (service: ServiceData) => {
        if (!service.last_run || !service.last_run.payload) return;
        
        const doc = new jsPDF();
        
        // --- PAGE 1: Executive Summary ---
        doc.setFont("helvetica", "bold");
        doc.setFontSize(16);
        doc.text(`Reporte de Transmisiones: ${service.description || service.name}`, 14, 20);
        
        doc.setFont("helvetica", "normal");
        doc.setFontSize(10);
        doc.text(`Identificador: ${service.name}`, 14, 28);
        doc.text(`Fecha Ejecucion: ${new Date(service.last_run.created_at).toLocaleString('es-ES')}`, 14, 34);
        doc.text(`Estado General: ${service.last_run.status.toUpperCase()}`, 14, 40);
        doc.text(`Mensaje: ${service.last_run.message || 'Sin mensaje'}`, 14, 46);
        
        doc.setDrawColor(220, 220, 220);
        doc.line(14, 52, 196, 52);
        
        doc.setFont("helvetica", "bold");
        doc.text("Cliente", 14, 60);
        doc.text("Estado", 90, 60);
        doc.text("Resumen de Transmision", 125, 60);
        
        doc.setFont("helvetica", "normal");
        let y = 68;
        
        service.last_run.payload.forEach((row: any) => {
            if (y > 270) {
                doc.addPage();
                y = 20;
                doc.setFont("helvetica", "bold");
                doc.text("Cliente", 14, y);
                doc.text("Estado", 90, y);
                doc.text("Resumen de Transmision", 125, y);
                doc.setFont("helvetica", "normal");
                y += 8;
            }
            
            doc.text(row.client.substring(0, 35), 14, y);
            doc.text(row.status === 'success' ? 'Correcto' : 'Error', 90, y);
            
            const messageText = row.message || '';
            const splitMessage = doc.splitTextToSize(messageText, 70);
            doc.text(splitMessage, 125, y);
            
            const linesCount = splitMessage.length;
            y += Math.max(8, linesCount * 5);
        });
        
        // --- FOLLOWING PAGES: Detailed Payload & SOAP Response per Client ---
        service.last_run.payload.forEach((row: any) => {
            doc.addPage();
            y = 20;
            
            // Client Header
            doc.setFont("helvetica", "bold");
            doc.setFontSize(13);
            doc.text(`Detalle de Trafico: ${row.client}`, 14, y);
            y += 8;
            
            doc.setFontSize(9);
            doc.text(`Fecha Ejecución: ${new Date(service.last_run!.created_at).toLocaleString('es-ES')}`, 14, y);
            y += 5;
            doc.text(`Estado: ${row.status.toUpperCase()}`, 14, y);
            y += 5;
            doc.text(`Respuesta: ${row.message || '—'}`, 14, y);
            y += 8;
            
            // Draw payload
            doc.setFontSize(9);
            doc.setFont("helvetica", "bold");
            doc.text("Payload Enviado (AVL GPS Positions):", 14, y);
            y += 5;
            doc.setFont("courier", "normal");
            doc.setFontSize(7.5);
            const payloadStr = safeStringify(row.transmission_payload, true) || 'Sin datos';
            const splitPayload = doc.splitTextToSize(payloadStr, 180);
            
            for (let i = 0; i < splitPayload.length; i++) {
                if (y > 280) {
                    doc.addPage();
                    y = 20;
                }
                doc.text(splitPayload[i], 14, y);
                y += 3.8;
            }
            
            // Draw response
            y += 6;
            if (y > 260) {
                doc.addPage();
                y = 20;
            }
            doc.setFont("helvetica", "bold");
            doc.setFontSize(9);
            doc.text("Respuesta del Servidor (SOAP/REST Response):", 14, y);
            y += 5;
            doc.setFont("courier", "normal");
            doc.setFontSize(7.5);
            const responseStr = safeStringify(row.transmission_response, true) || 'Sin respuesta';
            const splitResponse = doc.splitTextToSize(responseStr, 180);
            
            for (let i = 0; i < splitResponse.length; i++) {
                if (y > 280) {
                    doc.addPage();
                    y = 20;
                }
                doc.text(splitResponse[i], 14, y);
                y += 3.8;
            }
        });
        
        doc.save(`interaccion_${service.name.replace(/\s+/g, '_')}.pdf`);
    };

    const toggleRow = (idx: number) => {
        setExpandedClientIndex(expandedClientIndex === idx ? null : idx);
    };

    return (
        <AppLayout>
            <Head title="Panel de Telemetría" />
            <div className="flex h-full flex-1 flex-col gap-6 p-6">
                
                {/* Header */}
                <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight text-zinc-900 dark:text-zinc-50">Panel de Control & Telemetría</h1>
                        <p className="text-sm text-zinc-500 dark:text-zinc-400 mt-1">
                            Monitoreo en tiempo real de servicios SOAP/REST e integración de datos de GPS.
                        </p>
                    </div>
                    <Button 
                        variant="outline" 
                        onClick={handleRefresh} 
                        className="self-start sm:self-auto gap-2 border-zinc-200 dark:border-zinc-800"
                    >
                        <RefreshCw className="h-4 w-4" /> Actualizar Datos
                    </Button>
                </div>

                {/* Metrics Summary Grid */}
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    
                    <div className="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-950 flex items-center gap-4">
                        <div className="rounded-lg bg-orange-100 p-3 text-orange-600 dark:bg-orange-950/50 dark:text-orange-400">
                            <Server className="h-6 w-6" />
                        </div>
                        <div>
                            <p className="text-sm font-medium text-zinc-500 dark:text-zinc-400">Servicios Totales</p>
                            <h3 className="text-2xl font-bold text-zinc-900 dark:text-zinc-50">{metrics.total_services}</h3>
                        </div>
                    </div>

                    <div className="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-950 flex items-center gap-4">
                        <div className="rounded-lg bg-green-100 p-3 text-green-600 dark:bg-green-950/50 dark:text-green-400">
                            <CheckCircle2 className="h-6 w-6" />
                        </div>
                        <div>
                            <p className="text-sm font-medium text-zinc-500 dark:text-zinc-400">Servicios en Línea</p>
                            <h3 className="text-2xl font-bold text-zinc-900 dark:text-zinc-50">
                                {metrics.operational_services} <span className="text-sm font-normal text-zinc-400">/ {metrics.total_services}</span>
                            </h3>
                        </div>
                    </div>

                    <div className="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-950 flex items-center gap-4">
                        <div className="rounded-lg bg-blue-100 p-3 text-blue-600 dark:bg-blue-950/50 dark:text-blue-400">
                            <Activity className="h-6 w-6" />
                        </div>
                        <div>
                            <p className="text-sm font-medium text-zinc-500 dark:text-zinc-400">Fiabilidad Global</p>
                            <h3 className="text-2xl font-bold text-zinc-900 dark:text-zinc-50">{metrics.global_success_rate}%</h3>
                        </div>
                    </div>

                    <div className="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-950 flex items-center gap-4">
                        <div className="rounded-lg bg-zinc-100 p-3 text-zinc-600 dark:bg-zinc-900/50 dark:text-zinc-400">
                            <Clock className="h-6 w-6" />
                        </div>
                        <div>
                            <p className="text-sm font-medium text-zinc-500 dark:text-zinc-400">Latencia Promedio</p>
                            <h3 className="text-2xl font-bold text-zinc-900 dark:text-zinc-50">{metrics.global_avg_runtime} ms</h3>
                        </div>
                    </div>
                </div>

                {/* Main Content Layout */}
                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    
                    {/* Left & Middle Column: Services Telemetry Table */}
                    <div className="lg:col-span-2 flex flex-col gap-6">
                        <div className="rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-950 overflow-hidden">
                            <div className="border-b border-zinc-200 p-4 dark:border-zinc-800 flex items-center gap-2">
                                <Database className="h-5 w-5 text-orange-500" />
                                <h2 className="font-bold text-zinc-900 dark:text-zinc-50 text-base">Estado de Canales de Datos</h2>
                            </div>
                            <div className="overflow-x-auto">
                                <Table>
                                    <TableHeader className="bg-zinc-50 dark:bg-zinc-900/50">
                                        <TableRow>
                                            <TableHead className="font-semibold">Servicio / Comando</TableHead>
                                            <TableHead className="font-semibold text-center">Última Ejecución</TableHead>
                                            <TableHead className="font-semibold text-center">Último Éxito (OK)</TableHead>
                                            <TableHead className="font-semibold text-center">Fiabilidad (Últ. 50)</TableHead>
                                            <TableHead className="font-semibold text-center">Latencia</TableHead>
                                            <TableHead className="font-semibold text-right">Detalle</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {services.map((service) => (
                                            <TableRow key={service.id} className="hover:bg-zinc-50/50 dark:hover:bg-zinc-900/10">
                                                <TableCell className="py-4 font-medium">
                                                    <div>
                                                        <span className="text-zinc-900 dark:text-zinc-50 font-bold block">{service.description || service.name}</span>
                                                        <code className="text-[11px] text-zinc-400 font-mono mt-0.5 block">{service.name}</code>
                                                    </div>
                                                </TableCell>
                                                <TableCell className="text-center py-4">
                                                    {service.last_run ? (
                                                        <div className="flex flex-col items-center gap-1.5">
                                                            {service.last_run.status === 'success' && (
                                                                <Badge className="bg-green-100 text-green-700 hover:bg-green-200 dark:bg-green-950/30 dark:text-green-400 border-0 text-[10px] py-0 px-2">
                                                                    Correcto
                                                                </Badge>
                                                            )}
                                                            {service.last_run.status === 'partial' && (
                                                                <Badge className="bg-orange-100 text-orange-700 hover:bg-orange-200 dark:bg-orange-950/30 dark:text-orange-400 border-0 text-[10px] py-0 px-2">
                                                                    Advertencia
                                                                </Badge>
                                                            )}
                                                            {service.last_run.status === 'failure' && (
                                                                <Badge className="bg-red-100 text-red-700 hover:bg-red-200 dark:bg-red-950/30 dark:text-red-400 border-0 text-[10px] py-0 px-2">
                                                                    Error
                                                                </Badge>
                                                            )}
                                                            <span className="text-xs text-zinc-500 dark:text-zinc-400 block" title={service.last_run.created_at}>
                                                                {formatRelativeTime(service.last_run.created_at)}
                                                            </span>
                                                            <span className="text-[10px] text-zinc-400 dark:text-zinc-500 block font-mono mt-0.5">
                                                                {new Date(service.last_run.created_at).toLocaleString('es-ES')}
                                                            </span>
                                                        </div>
                                                    ) : (
                                                        <span className="text-xs text-zinc-400 dark:text-zinc-500 italic">Nunca ejecutado</span>
                                                     )}
                                                 </TableCell>
                                                <TableCell className="text-center text-xs text-zinc-700 dark:text-zinc-300 py-4" title={service.last_ok || ''}>
                                                    {service.last_ok ? (
                                                        <div className="flex flex-col items-center gap-1 text-green-600 dark:text-green-400 font-medium">
                                                            <div className="flex items-center justify-center gap-1">
                                                                <CheckCircle2 className="h-3.5 w-3.5" />
                                                                <span>{formatRelativeTime(service.last_ok)}</span>
                                                            </div>
                                                            <span className="text-[10px] text-zinc-450 dark:text-zinc-500 block font-mono">
                                                                {new Date(service.last_ok).toLocaleString('es-ES')}
                                                            </span>
                                                        </div>
                                                    ) : (
                                                        <span className="text-zinc-400 italic">Nunca</span>
                                                    )}
                                                </TableCell>
                                                <TableCell className="py-4">
                                                    <div className="flex flex-col items-center gap-1.5 w-24 mx-auto">
                                                        <span className={`text-xs font-bold px-1.5 py-0.5 rounded ${getReliabilityColor(service.reliability)}`}>
                                                            {service.reliability}%
                                                        </span>
                                                        <div className="w-full bg-zinc-100 rounded-full h-1.5 dark:bg-zinc-800 overflow-hidden">
                                                            <div 
                                                                className={`h-1.5 rounded-full ${getReliabilityBarColor(service.reliability)}`} 
                                                                style={{ width: `${service.reliability}%` }}
                                                            ></div>
                                                        </div>
                                                    </div>
                                                </TableCell>
                                                <TableCell className="text-center text-xs font-mono text-zinc-600 dark:text-zinc-300 py-4">
                                                    {service.avg_runtime} ms
                                                </TableCell>
                                                <TableCell className="text-right py-4">
                                                    {service.last_run ? (
                                                        <Button 
                                                            variant="outline" 
                                                            size="sm"
                                                            onClick={() => {
                                                                setSelectedService(service);
                                                                setExpandedClientIndex(null);
                                                            }}
                                                            className="text-orange-650 hover:text-white hover:bg-orange-500 border-orange-200 dark:border-orange-900 gap-1 text-[11px]"
                                                        >
                                                            <Activity className="h-3.5 w-3.5" /> Ver Última
                                                        </Button>
                                                    ) : (
                                                        <span className="text-[11px] text-zinc-400 dark:text-zinc-500 italic">Sin registros</span>
                                                    )}
                                                </TableCell>
                                            </TableRow>
                                        ))}
                                    </TableBody>
                                </Table>
                            </div>
                        </div>
                    </div>

                    {/* Right Column: Recent Activity Feed */}
                    <div className="flex flex-col gap-6">
                        <div className="rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-950 p-6 flex flex-col h-[550px] overflow-hidden">
                            <h3 className="text-lg font-bold text-zinc-900 dark:text-zinc-50 flex items-center gap-2 mb-4 border-b border-zinc-100 pb-3 dark:border-zinc-900">
                                <Terminal className="h-5 w-5 text-orange-500" /> Registro de Actividad (Logs)
                            </h3>
                            <div className="flex-1 overflow-y-auto pr-1 flex flex-col gap-4">
                                {recent_logs.length > 0 ? (
                                    recent_logs.map((log) => (
                                        <div 
                                            key={log.id} 
                                            className="text-xs p-3 rounded-lg border border-zinc-100 dark:border-zinc-900 bg-zinc-50/50 dark:bg-zinc-900/20 flex flex-col gap-1.5"
                                        >
                                            <div className="flex items-center justify-between gap-2">
                                                <span className="font-bold text-zinc-900 dark:text-zinc-100 truncate max-w-[150px]" title={log.service_name}>
                                                    {log.service_name}
                                                </span>
                                                <span className="text-[10px] text-zinc-400 shrink-0">
                                                    {formatRelativeTime(log.created_at)}
                                                </span>
                                            </div>
                                            <div className="flex items-center justify-between gap-2">
                                                <p className="text-zinc-600 dark:text-zinc-400 break-all flex-1 pr-2">
                                                    {log.message || 'Sin mensaje de log'}
                                                </p>
                                                <div className="shrink-0 flex items-center gap-1.5">
                                                    <span className="text-[10px] text-zinc-400 font-mono">{log.runtime_ms}ms</span>
                                                    {log.status === 'success' && <div className="h-2 w-2 rounded-full bg-green-500" />}
                                                    {log.status === 'partial' && <div className="h-2 w-2 rounded-full bg-orange-500" />}
                                                    {log.status === 'failure' && <div className="h-2 w-2 rounded-full bg-red-500" />}
                                                </div>
                                            </div>
                                        </div>
                                    ))
                                ) : (
                                    <div className="flex-1 flex flex-col items-center justify-center border border-dashed border-zinc-200 dark:border-zinc-800 rounded-lg py-12">
                                        <p className="text-sm text-zinc-400 dark:text-zinc-500 italic text-center">No hay registros aún</p>
                                    </div>
                                )}
                            </div>
                        </div>
                    </div>
                </div>

                {/* Modal detailed breakout */}
                {selectedService && (
                    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
                        <div className="bg-white dark:bg-zinc-950 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-2xl max-w-4xl w-full flex flex-col max-h-[85vh] overflow-hidden">
                            
                            {/* Modal Header */}
                            <div className="border-b border-zinc-100 dark:border-zinc-900 p-5 flex items-center justify-between">
                                <div>
                                    <h3 className="text-lg font-bold text-zinc-900 dark:text-zinc-50 flex items-center gap-2">
                                        <Terminal className="h-5 w-5 text-orange-500" />
                                        Última Interacción: {selectedService.description || selectedService.name}
                                    </h3>
                                    <p className="text-xs text-zinc-400 mt-1 font-mono">
                                        Comando: {selectedService.name}
                                    </p>
                                </div>
                                <button 
                                    onClick={() => setSelectedService(null)}
                                    className="text-zinc-400 hover:text-zinc-500 dark:hover:text-zinc-300 font-bold text-lg p-1 rounded-md hover:bg-zinc-100 dark:hover:bg-zinc-900 transition-colors"
                                >
                                    &times;
                                </button>
                            </div>
                            
                            {/* Modal Body */}
                            <div className="p-6 flex-1 overflow-y-auto flex flex-col gap-6">
                                
                                {/* Overview cards inside modal */}
                                <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div className="rounded-lg bg-zinc-50 p-4 dark:bg-zinc-900/40">
                                        <span className="text-[10px] uppercase font-bold text-zinc-400">Fecha Ejecución</span>
                                        <span className="text-sm font-semibold text-zinc-800 dark:text-zinc-100 mt-1 block">
                                            {selectedService.last_run ? new Date(selectedService.last_run.created_at).toLocaleString('es-ES') : '—'}
                                        </span>
                                    </div>
                                    <div className="rounded-lg bg-zinc-50 p-4 dark:bg-zinc-900/40">
                                        <span className="text-[10px] uppercase font-bold text-zinc-400">Estado General</span>
                                        <div className="mt-1 flex items-center gap-1.5">
                                            {selectedService.last_run?.status === 'success' && <Badge className="bg-green-100 text-green-700 dark:bg-green-950/40 dark:text-green-400 border-0 text-[10px]">CORRECTO</Badge>}
                                            {selectedService.last_run?.status === 'partial' && <Badge className="bg-orange-100 text-orange-700 dark:bg-orange-950/40 dark:text-orange-400 border-0 text-[10px]">ADVERTENCIA</Badge>}
                                            {selectedService.last_run?.status === 'failure' && <Badge className="bg-red-100 text-red-700 dark:bg-red-950/40 dark:text-red-400 border-0 text-[10px]">ERROR</Badge>}
                                        </div>
                                    </div>
                                    <div className="rounded-lg bg-zinc-50 p-4 dark:bg-zinc-900/40">
                                        <span className="text-[10px] uppercase font-bold text-zinc-400">Mensaje General</span>
                                        <span className="text-xs text-zinc-600 dark:text-zinc-300 mt-1 block truncate" title={selectedService.last_run?.message || ''}>
                                            {selectedService.last_run?.message || 'Sin mensaje descriptivo'}
                                        </span>
                                    </div>
                                </div>
                                
                                {/* Table of client interactions */}
                                <div>
                                    <h4 className="text-sm font-bold text-zinc-800 dark:text-zinc-200 mb-3 flex items-center gap-1.5">
                                        Detalle de Clientes enlazados
                                    </h4>
                                    {selectedService.last_run?.payload && selectedService.last_run.payload.length > 0 ? (
                                        <div className="border border-zinc-200 dark:border-zinc-800 rounded-lg overflow-hidden">
                                            <Table>
                                                <TableHeader className="bg-zinc-50 dark:bg-zinc-900/30">
                                                    <TableRow>
                                                        <TableHead className="font-semibold text-xs py-2 w-[40px]"></TableHead>
                                                        <TableHead className="font-semibold text-xs py-2">Cliente</TableHead>
                                                        <TableHead className="font-semibold text-xs py-2 text-center">Estado</TableHead>
                                                        <TableHead className="font-semibold text-xs py-2">Respuesta/Error Resumen</TableHead>
                                                        <TableHead className="font-semibold text-xs py-2 text-right">Tráfico Raw</TableHead>
                                                    </TableRow>
                                                </TableHeader>
                                                <TableBody>
                                                    {selectedService.last_run.payload.map((row: any, idx: number) => {
                                                        const isExpanded = expandedClientIndex === idx;
                                                        return (
                                                            <>
                                                                <TableRow key={idx} className="hover:bg-zinc-50/20 transition-colors">
                                                                    <TableCell className="py-3 text-center">
                                                                        <button 
                                                                            onClick={() => toggleRow(idx)}
                                                                            className="text-zinc-400 hover:text-zinc-600"
                                                                        >
                                                                            {isExpanded ? <ChevronUp className="h-4 w-4" /> : <ChevronDown className="h-4 w-4" />}
                                                                        </button>
                                                                    </TableCell>
                                                                    <TableCell className="font-medium text-xs py-3">{row.client}</TableCell>
                                                                    <TableCell className="text-center py-3">
                                                                        {row.status === 'success' ? (
                                                                            <span className="inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-medium bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400">
                                                                                OK
                                                                            </span>
                                                                        ) : (
                                                                            <span className="inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-medium bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400">
                                                                                ERROR
                                                                            </span>
                                                                        )}
                                                                    </TableCell>
                                                                    <TableCell className="text-xs text-zinc-500 dark:text-zinc-400 py-3 max-w-[250px] truncate" title={row.message}>
                                                                        {row.message || '—'}
                                                                    </TableCell>
                                                                    <TableCell className="text-right py-3">
                                                                        <Button
                                                                            variant="ghost"
                                                                            size="icon"
                                                                            onClick={() => toggleRow(idx)}
                                                                            className="h-7 w-7 text-zinc-500 hover:text-orange-500"
                                                                            title="Ver payload y respuesta"
                                                                        >
                                                                            <FileJson className="h-4 w-4" />
                                                                        </Button>
                                                                    </TableCell>
                                                                </TableRow>
                                                                
                                                                {/* Expandable sub-table row for raw payload/response */}
                                                                {isExpanded && (
                                                                    <TableRow className="bg-zinc-50/50 dark:bg-zinc-900/20 border-t-0">
                                                                        <TableCell colSpan={5} className="p-4">
                                                                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                                                <div className="flex flex-col gap-1.5">
                                                                                    <span className="text-[10px] font-bold text-zinc-450 uppercase tracking-wide flex items-center gap-1">
                                                                                        <Terminal className="h-3 w-3 text-green-500" /> Payload Enviado (Datos GPS)
                                                                                    </span>
                                                                                    <pre className="bg-zinc-950 text-green-400 p-3 rounded-lg font-mono text-[10px] overflow-auto max-h-[180px] border border-zinc-800 shadow-inner select-all">
                                                                                        {row.transmission_payload ? safeStringify(row.transmission_payload, true) : 'Sin datos de payload.'}
                                                                                    </pre>
                                                                                </div>
                                                                                <div className="flex flex-col gap-1.5">
                                                                                    <span className="text-[10px] font-bold text-zinc-450 uppercase tracking-wide flex items-center gap-1">
                                                                                        <Terminal className="h-3 w-3 text-blue-500" /> Respuesta del Servidor (SOAP/REST XML)
                                                                                    </span>
                                                                                    <pre className="bg-zinc-950 text-blue-400 p-3 rounded-lg font-mono text-[10px] overflow-auto max-h-[180px] border border-zinc-800 shadow-inner select-all">
                                                                                        {row.transmission_response ? safeStringify(row.transmission_response, true) : 'Sin respuesta de servidor.'}
                                                                                    </pre>
                                                                                </div>
                                                                            </div>
                                                                        </TableCell>
                                                                    </TableRow>
                                                                )}
                                                            </>
                                                        );
                                                    })}
                                                </TableBody>
                                            </Table>
                                        </div>
                                    ) : (
                                        <div className="text-center py-8 border border-dashed border-zinc-200 dark:border-zinc-800 rounded-lg">
                                            <p className="text-xs text-zinc-400 dark:text-zinc-500 italic">No hay detalles de clientes disponibles para esta interacción</p>
                                        </div>
                                    )}
                                </div>
                            </div>
                            
                            {/* Modal Footer (Exports) */}
                            <div className="border-t border-zinc-100 dark:border-zinc-900 p-4 bg-zinc-50/50 dark:bg-zinc-900/20 flex items-center justify-between gap-3">
                                <Button 
                                    variant="ghost" 
                                    onClick={() => setSelectedService(null)}
                                    className="text-xs border border-zinc-200 dark:border-zinc-800"
                                >
                                    Cerrar
                                </Button>
                                <div className="flex gap-2">
                                    <Button 
                                        variant="outline" 
                                        size="sm"
                                        onClick={() => exportToExcel(selectedService)}
                                        className="text-xs border-green-200 text-green-700 hover:text-green-805 hover:bg-green-50 dark:border-green-900 dark:hover:bg-green-950/20 dark:text-green-400 gap-1.5"
                                    >
                                        <Download className="h-3.5 w-3.5" /> Exportar Excel
                                    </Button>
                                    <Button 
                                        variant="default" 
                                        size="sm"
                                        onClick={() => exportToPdf(selectedService)}
                                        className="text-xs bg-red-650 hover:bg-red-500 text-white gap-1.5"
                                    >
                                        <Download className="h-3.5 w-3.5" /> Exportar PDF
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
