import React from 'react';
import { Indicator } from '../types';
import { 
  CheckCircle2, AlertCircle, XCircle, ArrowRight, Target, 
  TrendingUp, TrendingDown, Minus, Download, FileSpreadsheet, FileText
} from 'lucide-react';
import { exportIndicatorsToPDF, exportToExcel } from '../utils/exportUtils';

interface IndicatorStatusProps {
  indicators: Indicator[];
}

export const IndicatorStatus: React.FC<IndicatorStatusProps> = ({ indicators }) => {
  const getStatusIcon = (status: string) => {
    switch (status) {
      case 'on-track': return <CheckCircle2 className="w-5 h-5 text-emerald-500" />;
      case 'at-risk': return <AlertCircle className="w-5 h-5 text-amber-500" />;
      case 'behind': return <XCircle className="w-5 h-5 text-rose-500" />;
      default: return null;
    }
  };

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'on-track': return 'bg-emerald-50 text-emerald-700 border-emerald-100';
      case 'at-risk': return 'bg-amber-50 text-amber-700 border-amber-100';
      case 'behind': return 'bg-rose-50 text-rose-700 border-rose-100';
      default: return 'bg-slate-50 text-slate-700 border-slate-100';
    }
  };

  const handleExportExcel = () => {
    const data = indicators.map(ind => ({
      'Indicator Name': ind.name,
      'Category': ind.category,
      'Target': ind.target,
      'Actual': ind.actual,
      'Unit': ind.unit,
      'Achievement %': ind.achievedPercentage,
      'Gap': ind.gap,
      'Status': ind.status,
      'Last Updated': ind.lastUpdated
    }));
    exportToExcel(data, 'indicator-status-report');
  };

  return (
    <div className="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500">
      <div className="flex flex-col sm:flex-row items-center justify-between gap-4">
        <div>
          <h3 className="text-lg font-bold text-slate-900">Indicator Status Report</h3>
          <p className="text-sm text-slate-500">Detailed analysis of what's achieved and what needs to be done.</p>
        </div>
        <div className="flex items-center gap-3">
          <button 
            onClick={handleExportExcel}
            className="flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-50 transition-colors"
          >
            <FileSpreadsheet className="w-4 h-4" />
            Export Excel
          </button>
          <button 
            onClick={() => exportIndicatorsToPDF(indicators)}
            className="flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm font-medium hover:bg-indigo-700 transition-colors shadow-sm shadow-indigo-200"
          >
            <FileText className="w-4 h-4" />
            Export PDF
          </button>
        </div>
      </div>

      <div className="grid grid-cols-1 gap-6">
        {indicators.map((ind) => (
          <div key={ind.id} className="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden hover:shadow-md transition-all">
            <div className="p-6">
              <div className="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                <div className="flex-1">
                  <div className="flex items-center gap-3 mb-2">
                    {getStatusIcon(ind.status)}
                    <h4 className="text-base font-bold text-slate-900">{ind.name}</h4>
                    <span className={`px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider border ${getStatusColor(ind.status)}`}>
                      {ind.status}
                    </span>
                  </div>
                  <p className="text-sm text-slate-500 mb-4">{ind.category} • Last updated: {ind.lastUpdated}</p>
                  
                  <div className="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div className="p-3 bg-slate-50 rounded-xl border border-slate-100">
                      <p className="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Target</p>
                      <p className="text-lg font-bold text-slate-900">{ind.target} <span className="text-xs font-normal text-slate-400">{ind.unit}</span></p>
                    </div>
                    <div className="p-3 bg-slate-50 rounded-xl border border-slate-100">
                      <p className="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Actual</p>
                      <p className="text-lg font-bold text-slate-900">{ind.actual} <span className="text-xs font-normal text-slate-400">{ind.unit}</span></p>
                    </div>
                    <div className="p-3 bg-slate-50 rounded-xl border border-slate-100">
                      <p className="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Achievement</p>
                      <p className="text-lg font-bold text-indigo-600">{ind.achievedPercentage}%</p>
                    </div>
                    <div className="p-3 bg-slate-50 rounded-xl border border-slate-100">
                      <p className="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Gap</p>
                      <p className={`text-lg font-bold ${ind.gap > 0 ? 'text-rose-600' : 'text-emerald-600'}`}>
                        {ind.gap > 0 ? `+${ind.gap}` : ind.gap} <span className="text-xs font-normal text-slate-400">{ind.unit}</span>
                      </p>
                    </div>
                  </div>

                  {ind.disaggregation && (
                    <div className="mt-4 flex gap-4">
                      {ind.disaggregation.male !== undefined && (
                        <div className="flex items-center gap-2 text-xs">
                          <span className="w-2 h-2 rounded-full bg-blue-500"></span>
                          <span className="text-slate-500">Male:</span>
                          <span className="font-bold text-slate-900">{ind.disaggregation.male}</span>
                        </div>
                      )}
                      {ind.disaggregation.female !== undefined && (
                        <div className="flex items-center gap-2 text-xs">
                          <span className="w-2 h-2 rounded-full bg-pink-500"></span>
                          <span className="text-slate-500">Female:</span>
                          <span className="font-bold text-slate-900">{ind.disaggregation.female}</span>
                        </div>
                      )}
                      {ind.disaggregation.youth !== undefined && (
                        <div className="flex items-center gap-2 text-xs">
                          <span className="w-2 h-2 rounded-full bg-indigo-500"></span>
                          <span className="text-slate-500">Youth:</span>
                          <span className="font-bold text-slate-900">{ind.disaggregation.youth}</span>
                        </div>
                      )}
                    </div>
                  )}
                </div>

                <div className="lg:w-72 space-y-4">
                  <div className="space-y-2">
                    <div className="flex items-center justify-between text-xs">
                      <span className="font-bold text-slate-400 uppercase tracking-wider">Progress</span>
                      <span className="font-bold text-slate-900">{ind.achievedPercentage}%</span>
                    </div>
                    <div className="w-full bg-slate-100 rounded-full h-2.5 overflow-hidden">
                      <div 
                        className={`h-full rounded-full transition-all duration-1000 ${
                          ind.status === 'on-track' ? 'bg-emerald-500' : 
                          ind.status === 'at-risk' ? 'bg-amber-500' : 'bg-rose-500'
                        }`}
                        style={{ width: `${Math.min(ind.achievedPercentage, 100)}%` }}
                      />
                    </div>
                  </div>
                  
                  <div className="p-4 bg-slate-50 rounded-xl border border-slate-100">
                    <p className="text-xs font-bold text-slate-900 mb-2 flex items-center gap-2">
                      <ArrowRight className="w-3.5 h-3.5 text-indigo-500" />
                      What needs to be done:
                    </p>
                    <p className="text-xs text-slate-600 leading-relaxed">
                      {ind.gap > 0 
                        ? `Focus on reaching the remaining ${ind.gap} ${ind.unit} to meet the target. Current trend is ${ind.trend}.`
                        : `Target achieved! Maintain current performance levels and document best practices.`}
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        ))}
      </div>
    </div>
  );
};
