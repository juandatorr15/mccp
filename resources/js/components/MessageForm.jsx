import { useState } from 'react';
import axios from 'axios';

const CHANNELS = [
    { id: 'email', label: 'Email' },
    { id: 'slack', label: 'Slack (Webhook)' },
    { id: 'sms', label: 'SMS (SOAP)' },
];

export default function MessageForm({ onSuccess }) {
    const [title, setTitle] = useState('');
    const [content, setContent] = useState('');
    const [channels, setChannels] = useState(['email', 'slack', 'sms']);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);

    const toggleChannel = (id) => {
        setChannels((prev) =>
            prev.includes(id) ? prev.filter((c) => c !== id) : [...prev, id]
        );
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setError(null);

        if (channels.length === 0) {
            setError('Selecciona al menos un canal.');
            return;
        }

        setLoading(true);
        try {
            await axios.post('/api/messages', { title, content, channels });
            setTitle('');
            setContent('');
            setChannels(['email', 'slack', 'sms']);
            onSuccess();
        } catch (err) {
            const msg =
                err.response?.data?.message ||
                (err.response?.data?.errors
                    ? Object.values(err.response.data.errors).flat().join(', ')
                    : 'Error al enviar el mensaje.');
            setError(msg);
        } finally {
            setLoading(false);
        }
    };

    return (
        <form onSubmit={handleSubmit} className="bg-white rounded-lg shadow p-6 space-y-5">
            <h2 className="text-lg font-semibold text-gray-800">Nuevo Mensaje</h2>

            {error && (
                <div className="bg-red-50 border border-red-200 text-red-700 text-sm rounded p-3">
                    {error}
                </div>
            )}

            <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Titulo</label>
                <input
                    type="text"
                    value={title}
                    onChange={(e) => setTitle(e.target.value)}
                    required
                    className="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Titulo del mensaje"
                />
            </div>

            <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Contenido</label>
                <textarea
                    value={content}
                    onChange={(e) => setContent(e.target.value)}
                    required
                    rows={5}
                    className="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Escribe el contenido del mensaje..."
                />
            </div>

            <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">Canales</label>
                <div className="flex gap-4">
                    {CHANNELS.map((ch) => (
                        <label key={ch.id} className="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                            <input
                                type="checkbox"
                                checked={channels.includes(ch.id)}
                                onChange={() => toggleChannel(ch.id)}
                                className="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                            />
                            {ch.label}
                        </label>
                    ))}
                </div>
            </div>

            <button
                type="submit"
                disabled={loading}
                className="bg-blue-600 text-white px-5 py-2 rounded-md text-sm font-medium hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
            >
                {loading ? 'Enviando...' : 'Enviar Mensaje'}
            </button>
        </form>
    );
}
