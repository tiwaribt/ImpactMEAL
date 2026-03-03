import React, { useState, useEffect } from 'react';
import { Sidebar } from './components/Sidebar';
import { Dashboard } from './components/Dashboard';
import { Monitoring } from './components/Monitoring';
import { Qualitative } from './components/Qualitative';
import { Reporting } from './components/Reporting';
import { IndicatorStatus } from './components/IndicatorStatus';
import { GISView } from './components/GISView';
import { DataImport } from './components/DataImport';
import { DataManagement } from './components/DataManagement';
import { Login } from './components/Login';
import { Indicator, MonitoringEntry, QualitativeFeedback, User as UserType } from './types';
import { Bell, Search, User, Sparkles, AlertCircle, Download, Loader2, Database, LogOut } from 'lucide-react';
import { generateInfographicPDF } from './utils/exportUtils';

export default function App() {
  const [activeTab, setActiveTab] = useState('dashboard');
  const [indicators, setIndicators] = useState<Indicator[]>([]);
  const [entries, setEntries] = useState<MonitoringEntry[]>([]);
  const [feedback, setFeedback] = useState<QualitativeFeedback[]>([]);
  const [projects, setProjects] = useState<any[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [user, setUser] = useState<UserType | null>(null);

  useEffect(() => {
    if (user) {
      fetchData();
    }
  }, [user]);

  const fetchData = async () => {
    setIsLoading(true);
    try {
      const [indRes, entRes, feedRes, projRes] = await Promise.all([
        fetch('/api/indicators'),
        fetch('/api/monitoring'),
        fetch('/api/feedback'),
        fetch('/api/projects')
      ]);
      
      const [inds, ents, feeds, projs] = await Promise.all([
        indRes.json(),
        entRes.json(),
        feedRes.json(),
        projRes.json()
      ]);
      
      setIndicators(inds);
      setEntries(ents);
      setFeedback(feeds);
      setProjects(projs);
    } catch (error) {
      console.error("Failed to fetch data:", error);
    } finally {
      setIsLoading(false);
    }
  };

  const handleAddIndicator = async (newInd: any) => {
    const ind = {
      id: Math.random().toString(36).substr(2, 9),
      ...newInd
    };
    await fetch('/api/indicators', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(ind)
    });
    await fetchData();
  };

  const handleUpdateIndicator = async (id: string, update: any) => {
    await fetch(`/api/indicators/${id}`, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(update)
    });
    await fetchData();
  };

  const handleDeleteIndicator = async (id: string) => {
    if (!confirm('Are you sure you want to delete this indicator? All associated data will be lost.')) return;
    await fetch(`/api/indicators/${id}`, { method: 'DELETE' });
    await fetchData();
  };

  const handleAddEntry = async (newEntry: Omit<MonitoringEntry, 'id'>) => {
    const entry = {
      id: Math.random().toString(36).substr(2, 9),
      ...newEntry
    };
    
    try {
      await fetch('/api/monitoring', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(entry)
      });
      await fetchData();
    } catch (error) {
      console.error("Failed to add entry:", error);
    }
  };

  const handleUpdateEntry = async (id: string, update: any) => {
    await fetch(`/api/monitoring/${id}`, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(update)
    });
    await fetchData();
  };

  const handleDeleteEntry = async (id: string) => {
    if (!confirm('Are you sure you want to delete this entry?')) return;
    await fetch(`/api/monitoring/${id}`, { method: 'DELETE' });
    await fetchData();
  };

  const handleBulkImport = async (newEntries: Omit<MonitoringEntry, 'id'>[]) => {
    setIsLoading(true);
    try {
      for (const entry of newEntries) {
        await fetch('/api/monitoring', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            id: Math.random().toString(36).substr(2, 9),
            ...entry
          })
        });
      }
      await fetchData();
      setActiveTab('monitoring');
    } catch (error) {
      console.error("Failed bulk import:", error);
    } finally {
      setIsLoading(false);
    }
  };

  const handleAddFeedback = async (newFeedback: QualitativeFeedback) => {
    try {
      await fetch('/api/feedback', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(newFeedback)
      });
      await fetchData();
    } catch (error) {
      console.error("Failed to add feedback:", error);
    }
  };

  if (!user) {
    return <Login onLogin={setUser} />;
  }

  if (isLoading && indicators.length === 0) {
    return (
      <div className="min-h-screen bg-slate-50 flex items-center justify-center">
        <div className="flex flex-col items-center gap-4">
          <Loader2 className="w-10 h-10 text-indigo-600 animate-spin" />
          <p className="text-sm font-bold text-slate-500 uppercase tracking-widest">Loading ImpactMEAL...</p>
        </div>
      </div>
    );
  }

  const renderContent = () => {
    switch (activeTab) {
      case 'dashboard':
        return (
          <div id="infographic-dashboard">
            <Dashboard indicators={indicators} entries={entries} />
          </div>
        );
      case 'monitoring':
        return <Monitoring indicators={indicators} entries={entries} onAddEntry={handleAddEntry} />;
      case 'status':
        return <IndicatorStatus indicators={indicators} />;
      case 'gis':
        return <GISView indicators={indicators} entries={entries} />;
      case 'qualitative':
        return <Qualitative feedback={feedback} onAddFeedback={handleAddFeedback} />;
      case 'reporting':
        return <Reporting indicators={indicators} feedback={feedback} />;
      case 'management':
        return (
          <DataManagement 
            indicators={indicators} 
            entries={entries}
            projects={projects}
            onAddIndicator={handleAddIndicator}
            onUpdateIndicator={handleUpdateIndicator}
            onDeleteIndicator={handleDeleteIndicator}
            onAddEntry={handleAddEntry}
            onUpdateEntry={handleUpdateEntry}
            onDeleteEntry={handleDeleteEntry}
            onBulkImport={handleBulkImport}
          />
        );
      default:
        return <Dashboard indicators={indicators} entries={entries} />;
    }
  };

  const getTabTitle = () => {
    switch (activeTab) {
      case 'dashboard': return 'Program Overview';
      case 'monitoring': return 'Data Monitoring';
      case 'status': return 'Indicator Status Report';
      case 'gis': return 'GIS Reporting & GIS';
      case 'qualitative': return 'Qualitative Insights';
      case 'reporting': return 'Impact Reporting';
      case 'import': return 'Bulk Data Import';
      default: return 'Dashboard';
    }
  };

  return (
    <div className="min-h-screen bg-slate-50 flex">
      <Sidebar activeTab={activeTab} setActiveTab={setActiveTab} />
      
      <main className="flex-1 ml-64 p-8">
        {/* Header */}
        <header className="flex items-center justify-between mb-10">
          <div>
            <h2 className="text-2xl font-bold text-slate-900 tracking-tight">{getTabTitle()}</h2>
            <p className="text-sm text-slate-500 mt-1">Monitoring, Evaluation, Accountability, and Learning</p>
          </div>
          
          <div className="flex items-center gap-6">
            {activeTab === 'dashboard' && (
              <button 
                onClick={() => generateInfographicPDF('infographic-dashboard', 'ImpactMEAL Infographic')}
                className="flex items-center gap-2 px-3 py-1.5 bg-emerald-50 border border-emerald-100 rounded-lg text-xs font-bold text-emerald-700 uppercase tracking-wider hover:bg-emerald-100 transition-colors"
              >
                <Download className="w-4 h-4" />
                Export Infographic
              </button>
            )}
            
            <div className="hidden lg:flex items-center gap-2 px-3 py-1.5 bg-indigo-50 border border-indigo-100 rounded-lg">
              <Sparkles className="w-4 h-4 text-indigo-600" />
              <span className="text-xs font-bold text-indigo-700 uppercase tracking-wider">AI Powered Analysis</span>
            </div>
            
            <div className="flex items-center gap-4">
              <button className="p-2 text-slate-400 hover:text-slate-600 hover:bg-white rounded-xl transition-all relative">
                <Bell className="w-5 h-5" />
                <span className="absolute top-2 right-2 w-2 h-2 bg-rose-500 rounded-full border-2 border-slate-50"></span>
              </button>
              <div className="h-8 w-px bg-slate-200"></div>
              <div className="flex items-center gap-3 pl-2">
                <div className="text-right hidden sm:block">
                  <p className="text-sm font-bold text-slate-900">{user.username}</p>
                  <p className="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{user.role}</p>
                </div>
                <div className="group relative">
                  <div className="w-10 h-10 rounded-2xl bg-indigo-600 flex items-center justify-center text-white font-bold shadow-lg shadow-indigo-600/20 cursor-pointer">
                    {user.username.substring(0, 2).toUpperCase()}
                  </div>
                  <div className="absolute top-full right-0 mt-2 w-48 bg-white border border-slate-200 rounded-xl shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all z-50">
                    <button 
                      onClick={() => setUser(null)}
                      className="w-full flex items-center gap-2 px-4 py-3 text-sm text-rose-600 hover:bg-rose-50 transition-colors rounded-xl"
                    >
                      <LogOut className="w-4 h-4" />
                      Sign Out
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </header>

        {/* Alerts / Notifications */}
        {activeTab === 'dashboard' && (
          <div className="mb-8 p-4 bg-amber-50 border border-amber-100 rounded-2xl flex items-center gap-4 animate-in slide-in-from-top-4 duration-500">
            <div className="p-2 bg-amber-100 rounded-xl">
              <AlertCircle className="w-5 h-5 text-amber-600" />
            </div>
            <div>
              <p className="text-sm font-bold text-amber-900">Indicator Alert</p>
              <p className="text-xs text-amber-700">Training completion rate is currently 7% below target. Consider scheduling additional sessions.</p>
            </div>
            <button className="ml-auto text-xs font-bold text-amber-600 hover:text-amber-700 uppercase tracking-wider">View Details</button>
          </div>
        )}

        {/* Main Content Area */}
        <div className="max-w-7xl mx-auto">
          {renderContent()}
        </div>
      </main>
    </div>
  );
}
