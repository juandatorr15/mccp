import { useEffect, useState } from 'react';
import axios from 'axios';

const STATUS_COLORS = {
    sent: 'bg-green-100 text-green-800',
    failed: 'bg-red-100 text-red-800',
    pending: 'bg-yellow-100 text-yellow-800',
};

const MESSAGE_STATUS_COLORS = {
    completed: 'bg-green-100 text-green-800',
    failed: 'bg-red-100 text-red-800',
    processing: 'bg-blue-100 text-blue-800',
    pending: 'bg-yellow-100 text-yellow-800',
};

function SummaryModal({ summary, onClose }) {
    return (
        <div
            className="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
            onClick={onClose}
        >
            <div
                className="bg-white rounded-lg shadow-xl max-w-lg w-full mx-4 p-6"
                onClick={(e) => e.stopPropagation()}
            >
                <div className="flex items-center justify-between mb-4">
                    <h3 className="text-base font-semibold text-gray-800">Resumen IA</h3>
                    <button
                        onClick={onClose}
                        className="text-gray-400 hover:text-gray-600 text-xl leading-none"
                    >
                        &times;
                    </button>
                </div>
                <p className="text-gray-700 text-sm whitespace-pre-wrap leading-relaxed">
                    {summary}
                </p>
            </div>
        </div>
    );
}

export default function Dashboard() {
    const [messages, setMessages] = useState([]);
    const [loading, setLoading] = useState(true);
    const [selectedSummary, setSelectedSummary] = useState(null);

    useEffect(() => {
        axios
            .get('/api/messages')
            .then((res) => setMessages(res.data))
            .catch(console.error)
            .finally(() => setLoading(false));
    }, []);

    if (loading) {
        return (
            <div className="text-center py-10 text-gray-500">Cargando historial...</div>
        );
    }

    if (messages.length === 0) {
        return (
            <div className="bg-white rounded-lg shadow p-6 text-center text-gray-500">
                No hay mensajes enviados aún.
            </div>
        );
    }

    return (
        <>
            {selectedSummary && (
                <SummaryModal
                    summary={selectedSummary}
                    onClose={() => setSelectedSummary(null)}
                />
            )}
            <div className="bg-white rounded-lg shadow overflow-hidden">
                <div className="px-6 py-4 border-b">
                    <h2 className="text-lg font-semibold text-gray-800">Historial de Mensajes</h2>
                </div>
                <div className="overflow-x-auto">
                    <table className="w-full text-sm">
                        <thead className="bg-gray-50 text-gray-600">
                            <tr>
                                <th className="text-left px-4 py-3 font-medium">Fecha</th>
                                <th className="text-left px-4 py-3 font-medium">Título</th>
                                <th className="text-left px-4 py-3 font-medium">Resumen IA</th>
                                <th className="text-left px-4 py-3 font-medium">Estado</th>
                                <th className="text-left px-4 py-3 font-medium">Canales</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-gray-100">
                            {messages.map((msg) => (
                                <tr key={msg.id} className="hover:bg-gray-50">
                                    <td className="px-4 py-3 text-gray-600 whitespace-nowrap">
                                        {new Date(msg.created_at).toLocaleString('es-CO')}
                                    </td>
                                    <td className="px-4 py-3 text-gray-900 font-medium">
                                        {msg.title}
                                    </td>
                                    <td className="px-4 py-3 text-gray-600 max-w-xs">
                                        {msg.ai_summary ? (
                                            <button
                                                onClick={() => setSelectedSummary(msg.ai_summary)}
                                                className="text-left truncate block w-full text-blue-600 hover:text-blue-800 hover:underline cursor-pointer"
                                                title="Ver resumen completo"
                                            >
                                                {msg.ai_summary}
                                            </button>
                                        ) : (
                                            '—'
                                        )}
                                    </td>
                                    <td className="px-4 py-3">
                                        <span
                                            className={`inline-block px-2 py-1 rounded-full text-xs font-medium ${
                                                MESSAGE_STATUS_COLORS[msg.status] || 'bg-gray-100 text-gray-800'
                                            }`}
                                        >
                                            {msg.status}
                                        </span>
                                    </td>
                                    <td className="px-4 py-3">
                                        <div className="flex gap-1 flex-wrap">
                                            {msg.delivery_logs.map((log) => (
                                                <span
                                                    key={log.id}
                                                    title={log.error_message || ''}
                                                    className={`inline-block px-2 py-1 rounded-full text-xs font-medium ${
                                                        STATUS_COLORS[log.status] || 'bg-gray-100 text-gray-800'
                                                    }`}
                                                >
                                                    {log.channel}: {log.status}
                                                </span>
                                            ))}
                                        </div>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>
        </>
    );
}
