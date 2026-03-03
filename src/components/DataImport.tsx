import React, { useState } from 'react';
import Papa from 'papaparse';
import * as XLSX from 'xlsx';
import { Upload, FileSpreadsheet, FileText, CheckCircle2, AlertCircle, RefreshCw, ArrowRight, Settings } from 'lucide-react';
import { Indicator, MonitoringEntry } from '../types';

interface DataImportProps {
  indicators: Indicator[];
  onImport: (entries: Omit<MonitoringEntry, 'id'>[]) => void;
}

export const DataImport: React.FC<DataImportProps> = ({ indicators, onImport }) => {
  const [file, setFile] = useState<File | null>(null);
  const [data, setData] = useState<any[]>([]);
  const [mapping, setMapping] = useState<Record<string, string>>({});
  const [isImporting, setIsImporting] = useState(false);
  const [step, setStep] = useState<'upload' | 'mapping' | 'preview'>('upload');

  const systemFields = ['indicatorId', 'value', 'date', 'location', 'notes'];

  const handleFileUpload = (e: React.ChangeEvent<HTMLInputElement>) => {
    const uploadedFile = e.target.files?.[0];
    if (!uploadedFile) return;

    setFile(uploadedFile);
    const extension = uploadedFile.name.split('.').pop()?.toLowerCase();

    if (extension === 'csv') {
      Papa.parse(uploadedFile, {
        header: true,
        complete: (results) => {
          setData(results.data);
          setStep('mapping');
        }
      });
    } else if (extension === 'xlsx' || extension === 'xls') {
      const reader = new FileReader();
      reader.onload = (event) => {
        const bstr = event.target?.result;
        const wb = XLSX.read(bstr, { type: 'binary' });
        const wsname = wb.SheetNames[0];
        const ws = wb.Sheets[wsname];
        const json = XLSX.utils.sheet_to_json(ws);
        setData(json);
        setStep('mapping');
      };
      reader.readAsBinaryString(uploadedFile);
    }
  };

  const handleImport = () => {
    setIsImporting(true);
    
    const mappedEntries: Omit<MonitoringEntry, 'id'>[] = data.map(row => {
      const entry: any = {};
      Object.entries(mapping).forEach(([systemField, csvField]) => {
        entry[systemField] = row[csvField];
      });
      
      // Ensure numeric value
      if (entry.value) entry.value = parseFloat(entry.value);
      
      return entry as Omit<MonitoringEntry, 'id'>;
    }).filter(e => e.indicatorId && e.value && e.date);

    setTimeout(() => {
      onImport(mappedEntries);
      setIsImporting(false);
      setStep('upload');
      setFile(null);
      setData([]);
      setMapping({});
    }, 1500);
  };

  return (
    <div className="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500">
      <div className="bg-white p-8 rounded-2xl shadow-sm border border-slate-100">
        <div className="flex items-center gap-4 mb-8">
          <div className="p-3 bg-indigo-50 rounded-2xl">
            <Upload className="w-8 h-8 text-indigo-600" />
          </div>
          <div>
            <h2 className="text-xl font-bold text-slate-900">Data Import & Alignment</h2>
            <p className="text-sm text-slate-500">Import bulk monitoring data and align it with system parameters.</p>
          </div>
        </div>

        {step === 'upload' && (
          <div className="border-2 border-dashed border-slate-200 rounded-2xl p-12 text-center hover:border-indigo-300 transition-all group">
            <div className="flex flex-col items-center gap-4">
              <div className="p-4 bg-slate-50 rounded-full group-hover:bg-indigo-50 transition-colors">
                <FileSpreadsheet className="w-10 h-10 text-slate-400 group-hover:text-indigo-500 transition-colors" />
              </div>
              <div>
                <h3 className="text-lg font-bold text-slate-900">Upload CSV or Excel</h3>
                <p className="text-sm text-slate-500 max-w-xs mx-auto mt-1">
                  Drag and drop your reporting file here or click to browse.
                </p>
              </div>
              <label className="mt-4 px-6 py-2 bg-indigo-600 text-white rounded-xl text-sm font-medium hover:bg-indigo-700 transition-colors cursor-pointer shadow-sm shadow-indigo-200">
                Browse Files
                <input type="file" accept=".csv,.xlsx,.xls" className="hidden" onChange={handleFileUpload} />
              </label>
            </div>
          </div>
        )}

        {step === 'mapping' && (
          <div className="space-y-6 animate-in fade-in duration-300">
            <div className="flex items-center justify-between mb-4">
              <h3 className="text-sm font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                <Settings className="w-4 h-4 text-indigo-500" />
                Align System Parameters
              </h3>
              <button onClick={() => setStep('upload')} className="text-xs font-bold text-slate-400 hover:text-slate-600 uppercase tracking-wider">
                Back to Upload
              </button>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div className="space-y-4">
                {systemFields.map(field => (
                  <div key={field} className="flex items-center gap-4 p-4 bg-slate-50 rounded-xl border border-slate-100">
                    <div className="flex-1">
                      <p className="text-xs font-bold text-slate-900 capitalize">{field.replace('Id', '')}</p>
                      <p className="text-[10px] text-slate-500">System Parameter</p>
                    </div>
                    <ArrowRight className="w-4 h-4 text-slate-300" />
                    <select 
                      className="flex-1 px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
                      value={mapping[field] || ''}
                      onChange={(e) => setMapping({...mapping, [field]: e.target.value})}
                    >
                      <option value="">Select Column</option>
                      {Object.keys(data[0] || {}).map(col => (
                        <option key={col} value={col}>{col}</option>
                      ))}
                    </select>
                  </div>
                ))}
              </div>

              <div className="bg-slate-900 rounded-2xl p-6 text-slate-300">
                <h4 className="text-xs font-bold text-white uppercase tracking-wider mb-4">Import Preview</h4>
                <div className="space-y-3">
                  {data.slice(0, 3).map((row, i) => (
                    <div key={i} className="p-3 bg-slate-800 rounded-xl border border-slate-700 text-[10px] font-mono overflow-hidden truncate">
                      {JSON.stringify(row)}
                    </div>
                  ))}
                  <p className="text-[10px] text-slate-500 italic">Showing first 3 rows of {data.length} total rows.</p>
                </div>
                <button 
                  onClick={() => setStep('preview')}
                  className="w-full mt-6 py-2 bg-indigo-600 text-white rounded-xl text-sm font-bold hover:bg-indigo-700 transition-colors shadow-lg shadow-indigo-600/20"
                >
                  Continue to Preview
                </button>
              </div>
            </div>
          </div>
        )}

        {step === 'preview' && (
          <div className="space-y-6 animate-in fade-in duration-300">
            <div className="flex items-center justify-between mb-4">
              <h3 className="text-sm font-bold text-slate-900 uppercase tracking-wider">Final Review</h3>
              <button onClick={() => setStep('mapping')} className="text-xs font-bold text-slate-400 hover:text-slate-600 uppercase tracking-wider">
                Back to Mapping
              </button>
            </div>

            <div className="bg-emerald-50 border border-emerald-100 p-4 rounded-xl flex items-center gap-3 mb-6">
              <CheckCircle2 className="w-5 h-5 text-emerald-500" />
              <p className="text-sm text-emerald-700 font-medium">
                Successfully mapped {Object.keys(mapping).length} fields. Ready to import {data.length} records.
              </p>
            </div>

            <div className="bg-white border border-slate-200 rounded-xl overflow-hidden">
              <table className="w-full text-left text-xs">
                <thead className="bg-slate-50 border-b border-slate-200">
                  <tr>
                    {systemFields.map(f => <th key={f} className="px-4 py-2 font-bold text-slate-500 uppercase tracking-wider">{f}</th>)}
                  </tr>
                </thead>
                <tbody className="divide-y divide-slate-100">
                  {data.slice(0, 5).map((row, i) => (
                    <tr key={i}>
                      {systemFields.map(f => (
                        <td key={f} className="px-4 py-2 text-slate-600">
                          {row[mapping[f]] || '-'}
                        </td>
                      ))}
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>

            <div className="flex gap-4">
              <button 
                onClick={() => setStep('upload')}
                className="flex-1 px-6 py-3 border border-slate-200 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-50 transition-colors"
                disabled={isImporting}
              >
                Cancel
              </button>
              <button 
                onClick={handleImport}
                className="flex-[2] px-6 py-3 bg-indigo-600 text-white rounded-xl text-sm font-medium hover:bg-indigo-700 transition-colors shadow-lg shadow-indigo-600/20 flex items-center justify-center gap-2"
                disabled={isImporting}
              >
                {isImporting ? (
                  <>
                    <RefreshCw className="w-4 h-4 animate-spin" />
                    Importing Records...
                  </>
                ) : (
                  <>
                    <CheckCircle2 className="w-4 h-4" />
                    Confirm & Start Import
                  </>
                )}
              </button>
            </div>
          </div>
        )}
      </div>
    </div>
  );
};
