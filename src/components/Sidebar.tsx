import React from 'react';
import { LayoutDashboard, ClipboardList, MessageSquare, FileText, Settings, LogOut, Sparkles, Map as MapIcon, Target, Upload, Database } from 'lucide-react';

interface SidebarProps {
  activeTab: string;
  setActiveTab: (tab: string) => void;
}

export const Sidebar: React.FC<SidebarProps> = ({ activeTab, setActiveTab }) => {
  const menuItems = [
    { id: 'dashboard', label: 'Dashboard', icon: LayoutDashboard },
    { id: 'monitoring', label: 'Monitoring', icon: ClipboardList },
    { id: 'status', label: 'Indicator Status', icon: Target },
    { id: 'gis', label: 'GIS Reporting', icon: MapIcon },
    { id: 'management', label: 'Data Management', icon: Database },
    { id: 'qualitative', label: 'Qualitative', icon: MessageSquare },
    { id: 'reporting', label: 'Reporting', icon: FileText },
  ];

  return (
    <div className="w-64 h-screen bg-slate-900 text-slate-300 flex flex-col fixed left-0 top-0 z-50">
      <div className="p-8 flex items-center gap-3">
        <div className="p-2 bg-indigo-600 rounded-xl shadow-lg shadow-indigo-500/20">
          <Sparkles className="w-6 h-6 text-white" />
        </div>
        <h1 className="text-xl font-bold text-white tracking-tight">ImpactMEAL</h1>
      </div>

      <nav className="flex-1 px-4 py-6 space-y-2">
        {menuItems.map((item) => (
          <button
            key={item.id}
            onClick={() => setActiveTab(item.id)}
            className={`w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all ${
              activeTab === item.id 
                ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20' 
                : 'hover:bg-slate-800 hover:text-white'
            }`}
          >
            <item.icon className={`w-5 h-5 ${activeTab === item.id ? 'text-white' : 'text-slate-400'}`} />
            {item.label}
          </button>
        ))}
      </nav>

      <div className="p-6 border-t border-slate-800 space-y-4">
        <div className="bg-slate-800/50 p-4 rounded-2xl border border-slate-700">
          <div className="flex items-center gap-3 mb-2">
            <div className="w-8 h-8 rounded-full bg-slate-700 flex items-center justify-center text-xs font-bold text-slate-300">
              BA
            </div>
            <div>
              <p className="text-xs font-bold text-white">Balram</p>
              <p className="text-[10px] text-slate-500">MEAL Officer</p>
            </div>
          </div>
          <button className="w-full flex items-center gap-2 text-[10px] font-bold text-slate-400 hover:text-white transition-colors uppercase tracking-widest">
            <Settings className="w-3 h-3" />
            Settings
          </button>
        </div>
        <button className="w-full flex items-center gap-3 px-4 py-2 text-sm font-medium text-slate-500 hover:text-rose-400 transition-colors">
          <LogOut className="w-4 h-4" />
          Logout
        </button>
      </div>
    </div>
  );
};
