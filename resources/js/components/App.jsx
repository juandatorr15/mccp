import { useState } from 'react';
import MessageForm from './MessageForm';
import Dashboard from './Dashboard';

export default function App() {
    const [activeTab, setActiveTab] = useState('form');
    const [refreshKey, setRefreshKey] = useState(0);

    const handleMessageSent = () => {
        setRefreshKey((k) => k + 1);
        setActiveTab('dashboard');
    };

    return (
        <div className="min-h-screen bg-gray-50">
            <header className="bg-white shadow-sm border-b">
                <div className="max-w-5xl mx-auto px-4 py-4">
                    <h1 className="text-2xl font-bold text-gray-900">MCCP</h1>
                    <p className="text-sm text-gray-500">Multi-Channel Content Processor</p>
                </div>
            </header>

            <nav className="bg-white border-b">
                <div className="max-w-5xl mx-auto px-4 flex gap-4">
                    <button
                        onClick={() => setActiveTab('form')}
                        className={`py-3 px-1 border-b-2 text-sm font-medium ${
                            activeTab === 'form'
                                ? 'border-blue-500 text-blue-600'
                                : 'border-transparent text-gray-500 hover:text-gray-700'
                        }`}
                    >
                        Enviar Mensaje
                    </button>
                    <button
                        onClick={() => setActiveTab('dashboard')}
                        className={`py-3 px-1 border-b-2 text-sm font-medium ${
                            activeTab === 'dashboard'
                                ? 'border-blue-500 text-blue-600'
                                : 'border-transparent text-gray-500 hover:text-gray-700'
                        }`}
                    >
                        Historial
                    </button>
                </div>
            </nav>

            <main className="max-w-5xl mx-auto px-4 py-6">
                {activeTab === 'form' ? (
                    <MessageForm onSuccess={handleMessageSent} />
                ) : (
                    <Dashboard key={refreshKey} />
                )}
            </main>
        </div>
    );
}
