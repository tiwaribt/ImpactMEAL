import React, { useState } from 'react';
import { QualitativeFeedback } from '../types';
import { MessageSquare, Sparkles, Quote, Search, Plus, Calendar, User, Tag, Loader2 } from 'lucide-react';
import { format } from 'date-fns';
import { analyzeFeedback } from '../services/gemini';

interface QualitativeProps {
  feedback: QualitativeFeedback[];
  onAddFeedback: (feedback: QualitativeFeedback) => void;
}

export const Qualitative: React.FC<QualitativeProps> = ({ feedback, onAddFeedback }) => {
  const [isAdding, setIsAdding] = useState(false);
  const [isAnalyzing, setIsAnalyzing] = useState(false);
  const [searchTerm, setSearchTerm] = useState('');
  const [newFeedback, setNewFeedback] = useState({
    source: '',
    content: '',
    date: format(new Date(), 'yyyy-MM-dd')
  });

  const filteredFeedback = feedback.filter(f => 
    f.content.toLowerCase().includes(searchTerm.toLowerCase()) ||
    f.source.toLowerCase().includes(searchTerm.toLowerCase()) ||
    f.themes?.some(t => t.toLowerCase().includes(searchTerm.toLowerCase()))
  );

  const handleAdd = async (e: React.FormEvent) => {
    e.preventDefault();
    setIsAnalyzing(true);
    
    try {
      const analysis = await analyzeFeedback(newFeedback.content);
      const feedbackEntry: QualitativeFeedback = {
        id: Math.random().toString(36).substr(2, 9),
        ...newFeedback,
        ...analysis
      };
      onAddFeedback(feedbackEntry);
      setIsAdding(false);
      setNewFeedback({
        source: '',
        content: '',
        date: format(new Date(), 'yyyy-MM-dd')
      });
    } catch (error) {
      console.error("Analysis failed:", error);
      // Fallback without analysis if Gemini fails
      onAddFeedback({
        id: Math.random().toString(36).substr(2, 9),
        ...newFeedback,
        sentiment: 'neutral',
        themes: [],
        summary: 'Analysis unavailable'
      });
    } finally {
      setIsAnalyzing(false);
    }
  };

  const getSentimentColor = (sentiment?: string) => {
    switch (sentiment) {
      case 'positive': return 'text-emerald-600 bg-emerald-50 border-emerald-100';
      case 'negative': return 'text-rose-600 bg-rose-50 border-rose-100';
      default: return 'text-slate-600 bg-slate-50 border-slate-100';
    }
  };

  return (
    <div className="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-500">
      <div className="flex flex-col sm:flex-row items-center justify-between gap-4">
        <div className="relative w-full sm:w-96">
          <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
          <input
            type="text"
            placeholder="Search qualitative feedback..."
            className="w-full pl-10 pr-4 py-2 bg-white border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all"
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
          />
        </div>
        <button 
          onClick={() => setIsAdding(true)}
          className="w-full sm:w-auto flex items-center justify-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm font-medium hover:bg-indigo-700 transition-colors shadow-sm shadow-indigo-200"
        >
          <Plus className="w-4 h-4" />
          Add Feedback
        </button>
      </div>

      {isAdding && (
        <div className="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 flex items-center justify-center p-4">
          <div className="bg-white w-full max-w-lg rounded-2xl shadow-2xl overflow-hidden animate-in zoom-in-95 duration-200">
            <div className="px-6 py-4 border-bottom border-slate-100 flex items-center justify-between bg-slate-50/50">
              <div className="flex items-center gap-2">
                <Sparkles className="w-4 h-4 text-indigo-500" />
                <h3 className="font-semibold text-slate-900">Add Qualitative Feedback</h3>
              </div>
              <button onClick={() => setIsAdding(false)} className="text-slate-400 hover:text-slate-600">
                <Plus className="w-5 h-5 rotate-45" />
              </button>
            </div>
            <form onSubmit={handleAdd} className="p-6 space-y-4">
              <div className="space-y-1">
                <label className="text-xs font-semibold text-slate-500 uppercase tracking-wider">Source / Context</label>
                <input 
                  type="text"
                  placeholder="e.g. Focus Group Discussion, Interview with..."
                  className="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
                  value={newFeedback.source}
                  onChange={(e) => setNewFeedback({...newFeedback, source: e.target.value})}
                  required
                />
              </div>
              <div className="space-y-1">
                <label className="text-xs font-semibold text-slate-500 uppercase tracking-wider">Date</label>
                <input 
                  type="date"
                  className="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
                  value={newFeedback.date}
                  onChange={(e) => setNewFeedback({...newFeedback, date: e.target.value})}
                  required
                />
              </div>
              <div className="space-y-1">
                <label className="text-xs font-semibold text-slate-500 uppercase tracking-wider">Feedback Content</label>
                <textarea 
                  rows={5}
                  placeholder="Paste the qualitative feedback or story of change here..."
                  className="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
                  value={newFeedback.content}
                  onChange={(e) => setNewFeedback({...newFeedback, content: e.target.value})}
                  required
                />
                <p className="text-[10px] text-slate-400 flex items-center gap-1 mt-1">
                  <Sparkles className="w-3 h-3" />
                  AI will automatically analyze sentiment and extract themes.
                </p>
              </div>
              <div className="pt-4 flex gap-3">
                <button 
                  type="button"
                  onClick={() => setIsAdding(false)}
                  className="flex-1 px-4 py-2 border border-slate-200 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-50 transition-colors"
                  disabled={isAnalyzing}
                >
                  Cancel
                </button>
                <button 
                  type="submit"
                  className="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm font-medium hover:bg-indigo-700 transition-colors shadow-sm shadow-indigo-200 flex items-center justify-center gap-2"
                  disabled={isAnalyzing}
                >
                  {isAnalyzing ? (
                    <>
                      <Loader2 className="w-4 h-4 animate-spin" />
                      Analyzing...
                    </>
                  ) : (
                    <>
                      <Sparkles className="w-4 h-4" />
                      Analyze & Save
                    </>
                  )}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      <div className="grid grid-cols-1 gap-6">
        {filteredFeedback.map((item) => (
          <div key={item.id} className="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden hover:shadow-md transition-all group">
            <div className="p-6">
              <div className="flex flex-wrap items-start justify-between gap-4 mb-4">
                <div className="flex items-center gap-3">
                  <div className="p-2 bg-indigo-50 rounded-lg">
                    <Quote className="w-5 h-5 text-indigo-500" />
                  </div>
                  <div>
                    <h4 className="text-sm font-semibold text-slate-900">{item.source}</h4>
                    <div className="flex items-center gap-3 text-xs text-slate-400 mt-0.5">
                      <span className="flex items-center gap-1">
                        <Calendar className="w-3 h-3" />
                        {format(new Date(item.date), 'MMM dd, yyyy')}
                      </span>
                    </div>
                  </div>
                </div>
                <div className={`px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider border ${getSentimentColor(item.sentiment)}`}>
                  {item.sentiment}
                </div>
              </div>

              <div className="relative mb-6">
                <p className="text-slate-600 text-sm leading-relaxed italic">
                  "{item.content}"
                </p>
              </div>

              {item.summary && (
                <div className="bg-slate-50 rounded-xl p-4 mb-4 border border-slate-100">
                  <div className="flex items-center gap-2 mb-1">
                    <Sparkles className="w-3.5 h-3.5 text-indigo-500" />
                    <span className="text-[10px] font-bold text-slate-400 uppercase tracking-wider">AI Summary</span>
                  </div>
                  <p className="text-xs text-slate-700 font-medium">{item.summary}</p>
                </div>
              )}

              <div className="flex flex-wrap gap-2">
                {item.themes?.map((theme, idx) => (
                  <span key={idx} className="flex items-center gap-1.5 px-2.5 py-1 bg-slate-100 text-slate-600 rounded-lg text-[11px] font-medium">
                    <Tag className="w-3 h-3 text-slate-400" />
                    {theme}
                  </span>
                ))}
              </div>
            </div>
          </div>
        ))}

        {filteredFeedback.length === 0 && (
          <div className="bg-white rounded-2xl border border-dashed border-slate-200 p-12 text-center">
            <div className="flex flex-col items-center gap-3">
              <div className="p-4 bg-slate-50 rounded-full">
                <MessageSquare className="w-8 h-8 text-slate-300" />
              </div>
              <h3 className="text-slate-900 font-semibold">No feedback found</h3>
              <p className="text-sm text-slate-500 max-w-xs mx-auto">
                Start by adding qualitative feedback from your field visits, interviews, or focus groups.
              </p>
              <button 
                onClick={() => setIsAdding(true)}
                className="mt-2 px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm font-medium hover:bg-indigo-700 transition-colors"
              >
                Add Your First Feedback
              </button>
            </div>
          </div>
        )}
      </div>
    </div>
  );
};
