import React, { useState } from 'react';
import { Indicator, MonitoringEntry } from '../types';
import { 
  Plus, Edit2, Trash2, Save, X, Search, 
  ClipboardList, Target, Upload, Database
} from 'lucide-react';
import { DataImport } from './DataImport';
import { format } from 'date-fns';

interface DataManagementProps {
  indicators: Indicator[];
  entries: MonitoringEntry[];
  projects: any[];
  onAddIndicator: (ind: Omit<Indicator, 'id' | 'actual' | 'achievedPercentage' | 'gap' | 'status' | 'lastUpdated'>) => Promise<void>;
  onUpdateIndicator: (id: string, ind: Partial<Indicator>) => Promise<void>;
  onDeleteIndicator: (id: string) => Promise<void>;
  onAddEntry: (entry: Omit<MonitoringEntry, 'id'>) => Promise<void>;
  onUpdateEntry: (id: string, entry: Partial<MonitoringEntry>) => Promise<void>;
  onDeleteEntry: (id: string) => Promise<void>;
  onBulkImport: (entries: Omit<MonitoringEntry, 'id'>[]) => Promise<void>;
}

export const DataManagement: React.FC<DataManagementProps> = ({
  indicators,
  entries,
  projects,
  onAddIndicator,
  onUpdateIndicator,
  onDeleteIndicator,
  onAddEntry,
  onUpdateEntry,
  onDeleteEntry,
  onBulkImport
}) => {
  const [activeSubTab, setActiveSubTab] = useState<'indicators' | 'entries' | 'import'>('indicators');
  const [editingId, setEditingId] = useState<string | null>(null);
  const [editForm, setEditForm] = useState<any>({});
  const [searchTerm, setSearchTerm] = useState('');

  const handleEdit = (item: any) => {
    setEditingId(item.id);
    setEditForm(item);
  };

  const handleSaveIndicator = async () => {
    if (editingId === 'new') {
      await onAddIndicator(editForm);
    } else {
      await onUpdateIndicator(editingId!, editForm);
    }
    setEditingId(null);
  };

  const handleSaveEntry = async () => {
    if (editingId === 'new') {
      await onAddEntry(editForm);
    } else {
      await onUpdateEntry(editingId!, editForm);
    }
    setEditingId(null);
  };

  const filteredIndicators = indicators.filter(ind => 
    ind.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
    ind.category.toLowerCase().includes(searchTerm.toLowerCase())
  );

  const filteredEntries = entries.filter(entry => {
    const indicator = indicators.find(i => i.id === entry.indicatorId);
    return (
      indicator?.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
      entry.location.toLowerCase().includes(searchTerm.toLowerCase())
    );
  });

  return (
    <div className="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-500">
      <div className="flex items-center gap-1 bg-white p-1 rounded-2xl border border-slate-100 shadow-sm w-fit">
        <button 
          onClick={() => setActiveSubTab('indicators')}
          className={`flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium transition-all ${
            activeSubTab === 'indicators' ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-500 hover:bg-slate-50'
          }`}
        >
          <Target className="w-4 h-4" />
          Indicators
        </button>
        <button 
          onClick={() => setActiveSubTab('entries')}
          className={`flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium transition-all ${
            activeSubTab === 'entries' ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-500 hover:bg-slate-50'
          }`}
        >
          <ClipboardList className="w-4 h-4" />
          Monitoring Data
        </button>
        <button 
          onClick={() => setActiveSubTab('import')}
          className={`flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium transition-all ${
            activeSubTab === 'import' ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-500 hover:bg-slate-50'
          }`}
        >
          <Upload className="w-4 h-4" />
          Bulk Upload
        </button>
      </div>

      {activeSubTab !== 'import' && (
        <div className="flex flex-col sm:flex-row items-center justify-between gap-4">
          <div className="relative w-full sm:w-96">
            <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
            <input
              type="text"
              placeholder={`Search ${activeSubTab}...`}
              className="w-full pl-10 pr-4 py-2 bg-white border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
            />
          </div>
          <button 
            onClick={() => {
              setEditingId('new');
              setEditForm(activeSubTab === 'indicators' ? {
                name: '', target: 0, unit: '', category: 'Outreach'
              } : {
                indicatorId: indicators[0]?.id || '', value: 0, date: format(new Date(), 'yyyy-MM-dd'), location: '', notes: ''
              });
            }}
            className="w-full sm:w-auto flex items-center justify-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm font-medium hover:bg-indigo-700 transition-colors shadow-sm"
          >
            <Plus className="w-4 h-4" />
            Add New {activeSubTab === 'indicators' ? 'Indicator' : 'Entry'}
          </button>
        </div>
      )}

      {activeSubTab === 'indicators' && (
        <div className="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
          <table className="w-full text-left border-collapse">
            <thead>
              <tr className="bg-slate-50/50 border-b border-slate-100">
                <th className="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Indicator Name</th>
                <th className="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Category</th>
                <th className="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Target</th>
                <th className="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Unit</th>
                <th className="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider text-right">Actions</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100">
              {editingId === 'new' && (
                <tr className="bg-indigo-50/30">
                  <td className="px-6 py-4">
                    <select 
                      className="w-full px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-sm"
                      value={editForm.projectId}
                      onChange={(e) => setEditForm({...editForm, projectId: e.target.value})}
                    >
                      <option value="">Select Project</option>
                      {projects.map(p => <option key={p.id} value={p.id}>{p.name}</option>)}
                    </select>
                    <input 
                      className="w-full px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-sm mt-2"
                      value={editForm.name}
                      onChange={(e) => setEditForm({...editForm, name: e.target.value})}
                      placeholder="Indicator Name"
                    />
                  </td>
                  <td className="px-6 py-4">
                    <select 
                      className="w-full px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-sm"
                      value={editForm.category}
                      onChange={(e) => setEditForm({...editForm, category: e.target.value})}
                    >
                      <option value="Outreach">Outreach</option>
                      <option value="Capacity Building">Capacity Building</option>
                      <option value="Accountability">Accountability</option>
                    </select>
                  </td>
                  <td className="px-6 py-4">
                    <input 
                      type="number"
                      className="w-24 px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-sm"
                      value={editForm.target}
                      onChange={(e) => setEditForm({...editForm, target: parseFloat(e.target.value)})}
                    />
                  </td>
                  <td className="px-6 py-4">
                    <input 
                      className="w-24 px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-sm"
                      value={editForm.unit}
                      onChange={(e) => setEditForm({...editForm, unit: e.target.value})}
                      placeholder="Unit"
                    />
                  </td>
                  <td className="px-6 py-4 text-right space-x-2">
                    <button onClick={handleSaveIndicator} className="p-2 text-emerald-600 hover:bg-emerald-50 rounded-lg"><Save className="w-4 h-4" /></button>
                    <button onClick={() => setEditingId(null)} className="p-2 text-slate-400 hover:bg-slate-100 rounded-lg"><X className="w-4 h-4" /></button>
                  </td>
                </tr>
              )}
              {filteredIndicators.map((ind) => (
                <tr key={ind.id} className="hover:bg-slate-50/50 transition-colors">
                  {editingId === ind.id ? (
                    <>
                      <td className="px-6 py-4">
                        <select 
                          className="w-full px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-sm"
                          value={editForm.projectId}
                          onChange={(e) => setEditForm({...editForm, projectId: e.target.value})}
                        >
                          <option value="">Select Project</option>
                          {projects.map(p => <option key={p.id} value={p.id}>{p.name}</option>)}
                        </select>
                        <input 
                          className="w-full px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-sm mt-2"
                          value={editForm.name}
                          onChange={(e) => setEditForm({...editForm, name: e.target.value})}
                        />
                      </td>
                      <td className="px-6 py-4">
                        <select 
                          className="w-full px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-sm"
                          value={editForm.category}
                          onChange={(e) => setEditForm({...editForm, category: e.target.value})}
                        >
                          <option value="Outreach">Outreach</option>
                          <option value="Capacity Building">Capacity Building</option>
                          <option value="Accountability">Accountability</option>
                        </select>
                      </td>
                      <td className="px-6 py-4">
                        <input 
                          type="number"
                          className="w-24 px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-sm"
                          value={editForm.target}
                          onChange={(e) => setEditForm({...editForm, target: parseFloat(e.target.value)})}
                        />
                      </td>
                      <td className="px-6 py-4">
                        <input 
                          className="w-24 px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-sm"
                          value={editForm.unit}
                          onChange={(e) => setEditForm({...editForm, unit: e.target.value})}
                        />
                      </td>
                      <td className="px-6 py-4 text-right space-x-2">
                        <button onClick={handleSaveIndicator} className="p-2 text-emerald-600 hover:bg-emerald-50 rounded-lg"><Save className="w-4 h-4" /></button>
                        <button onClick={() => setEditingId(null)} className="p-2 text-slate-400 hover:bg-slate-100 rounded-lg"><X className="w-4 h-4" /></button>
                      </td>
                    </>
                  ) : (
                    <>
                      <td className="px-6 py-4">
                        <div className="text-sm font-medium text-slate-900">{ind.name}</div>
                        <div className="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-0.5">
                          {projects.find(p => p.id === ind.projectId)?.name || 'No Project'}
                        </div>
                      </td>
                      <td className="px-6 py-4">
                        <span className="px-2 py-1 bg-slate-100 text-slate-600 rounded-lg text-[10px] font-bold uppercase tracking-wider">
                          {ind.category}
                        </span>
                      </td>
                      <td className="px-6 py-4 text-sm font-bold text-slate-700">{ind.target}</td>
                      <td className="px-6 py-4 text-sm text-slate-500">{ind.unit}</td>
                      <td className="px-6 py-4 text-right space-x-2">
                        <button onClick={() => handleEdit(ind)} className="p-2 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all">
                          <Edit2 className="w-4 h-4" />
                        </button>
                        <button onClick={() => onDeleteIndicator(ind.id)} className="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-all">
                          <Trash2 className="w-4 h-4" />
                        </button>
                      </td>
                    </>
                  )}
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}

      {activeSubTab === 'entries' && (
        <div className="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
          <table className="w-full text-left border-collapse">
            <thead>
              <tr className="bg-slate-50/50 border-b border-slate-100">
                <th className="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Indicator</th>
                <th className="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Value</th>
                <th className="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Location</th>
                <th className="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Date</th>
                <th className="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider text-right">Actions</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100">
              {editingId === 'new' && (
                <tr className="bg-indigo-50/30">
                  <td className="px-6 py-4">
                    <select 
                      className="w-full px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-sm"
                      value={editForm.indicatorId}
                      onChange={(e) => setEditForm({...editForm, indicatorId: e.target.value})}
                    >
                      {indicators.map(i => <option key={i.id} value={i.id}>{i.name}</option>)}
                    </select>
                  </td>
                  <td className="px-6 py-4">
                    <input 
                      type="number"
                      className="w-24 px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-sm"
                      value={editForm.value}
                      onChange={(e) => setEditForm({...editForm, value: parseFloat(e.target.value)})}
                    />
                    <div className="mt-2 grid grid-cols-2 gap-2">
                      <input 
                        type="number" 
                        placeholder="M" 
                        className="px-2 py-1 text-[10px] border rounded"
                        value={editForm.disaggregation?.male || ''}
                        onChange={(e) => setEditForm({...editForm, disaggregation: { ...editForm.disaggregation, male: parseFloat(e.target.value) }})}
                      />
                      <input 
                        type="number" 
                        placeholder="F" 
                        className="px-2 py-1 text-[10px] border rounded"
                        value={editForm.disaggregation?.female || ''}
                        onChange={(e) => setEditForm({...editForm, disaggregation: { ...editForm.disaggregation, female: parseFloat(e.target.value) }})}
                      />
                    </div>
                  </td>
                  <td className="px-6 py-4">
                    <input 
                      className="w-full px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-sm"
                      value={editForm.location}
                      onChange={(e) => setEditForm({...editForm, location: e.target.value})}
                      placeholder="Location"
                    />
                  </td>
                  <td className="px-6 py-4">
                    <input 
                      type="date"
                      className="w-full px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-sm"
                      value={editForm.date}
                      onChange={(e) => setEditForm({...editForm, date: e.target.value})}
                    />
                  </td>
                  <td className="px-6 py-4 text-right space-x-2">
                    <button onClick={handleSaveEntry} className="p-2 text-emerald-600 hover:bg-emerald-50 rounded-lg"><Save className="w-4 h-4" /></button>
                    <button onClick={() => setEditingId(null)} className="p-2 text-slate-400 hover:bg-slate-100 rounded-lg"><X className="w-4 h-4" /></button>
                  </td>
                </tr>
              )}
              {filteredEntries.map((entry) => {
                const indicator = indicators.find(i => i.id === entry.indicatorId);
                return (
                  <tr key={entry.id} className="hover:bg-slate-50/50 transition-colors">
                    {editingId === entry.id ? (
                      <>
                        <td className="px-6 py-4">
                          <select 
                            className="w-full px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-sm"
                            value={editForm.indicatorId}
                            onChange={(e) => setEditForm({...editForm, indicatorId: e.target.value})}
                          >
                            {indicators.map(i => <option key={i.id} value={i.id}>{i.name}</option>)}
                          </select>
                        </td>
                        <td className="px-6 py-4">
                          <input 
                            type="number"
                            className="w-24 px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-sm"
                            value={editForm.value}
                            onChange={(e) => setEditForm({...editForm, value: parseFloat(e.target.value)})}
                          />
                          <div className="mt-2 grid grid-cols-2 gap-2">
                            <input 
                              type="number" 
                              placeholder="M" 
                              className="px-2 py-1 text-[10px] border rounded"
                              value={editForm.disaggregation?.male || ''}
                              onChange={(e) => setEditForm({...editForm, disaggregation: { ...editForm.disaggregation, male: parseFloat(e.target.value) }})}
                            />
                            <input 
                              type="number" 
                              placeholder="F" 
                              className="px-2 py-1 text-[10px] border rounded"
                              value={editForm.disaggregation?.female || ''}
                              onChange={(e) => setEditForm({...editForm, disaggregation: { ...editForm.disaggregation, female: parseFloat(e.target.value) }})}
                            />
                          </div>
                        </td>
                        <td className="px-6 py-4">
                          <input 
                            className="w-full px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-sm"
                            value={editForm.location}
                            onChange={(e) => setEditForm({...editForm, location: e.target.value})}
                          />
                        </td>
                        <td className="px-6 py-4">
                          <input 
                            type="date"
                            className="w-full px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-sm"
                            value={editForm.date}
                            onChange={(e) => setEditForm({...editForm, date: e.target.value})}
                          />
                        </td>
                        <td className="px-6 py-4 text-right space-x-2">
                          <button onClick={handleSaveEntry} className="p-2 text-emerald-600 hover:bg-emerald-50 rounded-lg"><Save className="w-4 h-4" /></button>
                          <button onClick={() => setEditingId(null)} className="p-2 text-slate-400 hover:bg-slate-100 rounded-lg"><X className="w-4 h-4" /></button>
                        </td>
                      </>
                    ) : (
                      <>
                        <td className="px-6 py-4 text-sm font-medium text-slate-900">{indicator?.name}</td>
                        <td className="px-6 py-4 text-sm font-bold text-slate-700">{entry.value}</td>
                        <td className="px-6 py-4 text-sm text-slate-500">{entry.location}</td>
                        <td className="px-6 py-4 text-sm text-slate-500">{format(new Date(entry.date), 'MMM dd, yyyy')}</td>
                        <td className="px-6 py-4 text-right space-x-2">
                          <button onClick={() => handleEdit(entry)} className="p-2 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all">
                            <Edit2 className="w-4 h-4" />
                          </button>
                          <button onClick={() => onDeleteEntry(entry.id)} className="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-all">
                            <Trash2 className="w-4 h-4" />
                          </button>
                        </td>
                      </>
                    )}
                  </tr>
                );
              })}
            </tbody>
          </table>
        </div>
      )}

      {activeSubTab === 'import' && (
        <DataImport indicators={indicators} onImport={onBulkImport} />
      )}
    </div>
  );
};
