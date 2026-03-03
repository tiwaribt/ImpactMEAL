import React, { useState } from 'react';
import { Indicator, QualitativeFeedback } from '../types';
import { FileText, Sparkles, Download, RefreshCw, Loader2, CheckCircle, AlertCircle } from 'lucide-react';
import Markdown from 'react-markdown';
import { generateMEALReport } from '../services/gemini';

interface ReportingProps {
  indicators: Indicator[];
  feedback: QualitativeFeedback[];
}

export const Reporting: React.FC<ReportingProps> = ({ indicators, feedback }) => {
  const [isGenerating, setIsGenerating] = useState(false);
  const [report, setReport] = useState<string | null>(null);
  const [period, setPeriod] = useState('February 2026');

  const handleGenerate = async () => {
    setIsGenerating(true);
    try {
      const generatedReport = await generateMEALReport(indicators, feedback, period);
      setReport(generatedReport);
    } catch (error) {
      console.error("Report generation failed:", error);
      setReport("## Error\nFailed to generate report. Please try again.");
    } finally {
      setIsGenerating(false);
    }
  };

  return (
    <div className="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500">
      <div className="bg-white p-8 rounded-2xl shadow-sm border border-slate-100">
        <div className="flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
          <div className="flex items-center gap-4">
            <div className="p-3 bg-indigo-50 rounded-2xl">
              <FileText className="w-8 h-8 text-indigo-600" />
            </div>
            <div>
              <h2 className="text-xl font-bold text-slate-900">AI Report Generator</h2>
              <p className="text-sm text-slate-500">Generate a comprehensive MEAL report using AI analysis</p>
            </div>
          </div>
          <div className="flex items-center gap-3 w-full md:w-auto">
            <div className="flex-1 md:flex-none">
              <label className="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Reporting Period</label>
              <input 
                type="text"
                className="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
                value={period}
                onChange={(e) => setPeriod(e.target.value)}
              />
            </div>
            <button 
              onClick={handleGenerate}
              disabled={isGenerating}
              className="mt-5 flex-1 md:flex-none flex items-center justify-center gap-2 px-6 py-2 bg-indigo-600 text-white rounded-xl text-sm font-medium hover:bg-indigo-700 transition-colors shadow-sm shadow-indigo-200 disabled:opacity-50"
            >
              {isGenerating ? (
                <>
                  <Loader2 className="w-4 h-4 animate-spin" />
                  Generating...
                </>
              ) : (
                <>
                  <Sparkles className="w-4 h-4" />
                  {report ? 'Regenerate Report' : 'Generate Report'}
                </>
              )}
            </button>
          </div>
        </div>

        {!report && !isGenerating && (
          <div className="mt-12 grid grid-cols-1 md:grid-cols-3 gap-6">
            <div className="p-6 bg-slate-50 rounded-2xl border border-slate-100">
              <div className="flex items-center gap-2 mb-3">
                <CheckCircle className="w-4 h-4 text-emerald-500" />
                <h4 className="text-sm font-bold text-slate-900 uppercase tracking-wider">Quantitative Data</h4>
              </div>
              <p className="text-xs text-slate-500 leading-relaxed">
                {indicators.length} indicators and their achievement levels will be analyzed.
              </p>
            </div>
            <div className="p-6 bg-slate-50 rounded-2xl border border-slate-100">
              <div className="flex items-center gap-2 mb-3">
                <CheckCircle className="w-4 h-4 text-emerald-500" />
                <h4 className="text-sm font-bold text-slate-900 uppercase tracking-wider">Qualitative Data</h4>
              </div>
              <p className="text-xs text-slate-500 leading-relaxed">
                {feedback.length} qualitative feedback entries will be synthesized into themes.
              </p>
            </div>
            <div className="p-6 bg-slate-50 rounded-2xl border border-slate-100">
              <div className="flex items-center gap-2 mb-3">
                <Sparkles className="w-4 h-4 text-indigo-500" />
                <h4 className="text-sm font-bold text-slate-900 uppercase tracking-wider">AI Synthesis</h4>
              </div>
              <p className="text-xs text-slate-500 leading-relaxed">
                Gemini will identify trends, successes, and provide strategic recommendations.
              </p>
            </div>
          </div>
        )}
      </div>

      {report && (
        <div className="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden animate-in fade-in zoom-in-95 duration-500">
          <div className="px-8 py-4 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
            <div className="flex items-center gap-2">
              <FileText className="w-4 h-4 text-slate-400" />
              <span className="text-xs font-bold text-slate-500 uppercase tracking-wider">MEAL Report Preview</span>
            </div>
            <button className="flex items-center gap-2 px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-xs font-medium text-slate-600 hover:bg-slate-50 transition-colors">
              <Download className="w-3.5 h-3.5" />
              Download PDF
            </button>
          </div>
          <div className="p-12 max-w-4xl mx-auto">
            <div className="markdown-body prose prose-slate prose-sm sm:prose-base max-w-none">
              <Markdown>{report}</Markdown>
            </div>
          </div>
        </div>
      )}

      {isGenerating && (
        <div className="bg-white rounded-2xl border border-slate-100 p-24 flex flex-col items-center justify-center gap-6 text-center">
          <div className="relative">
            <div className="w-16 h-16 border-4 border-indigo-100 border-t-indigo-600 rounded-full animate-spin" />
            <Sparkles className="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-6 h-6 text-indigo-600 animate-pulse" />
          </div>
          <div>
            <h3 className="text-lg font-bold text-slate-900">Analyzing Project Data...</h3>
            <p className="text-sm text-slate-500 mt-1">Gemini is synthesizing quantitative metrics and qualitative insights into a comprehensive report.</p>
          </div>
          <div className="flex flex-wrap justify-center gap-4 mt-4">
            <div className="flex items-center gap-2 text-xs text-slate-400">
              <RefreshCw className="w-3 h-3 animate-spin" />
              Processing Indicators
            </div>
            <div className="flex items-center gap-2 text-xs text-slate-400">
              <RefreshCw className="w-3 h-3 animate-spin" />
              Extracting Themes
            </div>
            <div className="flex items-center gap-2 text-xs text-slate-400">
              <RefreshCw className="w-3 h-3 animate-spin" />
              Drafting Recommendations
            </div>
          </div>
        </div>
      )}
    </div>
  );
};
