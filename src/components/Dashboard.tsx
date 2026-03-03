import React from 'react';
import { 
  BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer,
  LineChart, Line, PieChart, Pie, Cell
} from 'recharts';
import { Indicator, MonitoringEntry } from '../types';
import { TrendingUp, TrendingDown, Minus, Target, Users, CheckCircle, Clock, AlertCircle } from 'lucide-react';

interface DashboardProps {
  indicators: Indicator[];
  entries: MonitoringEntry[];
}

const COLORS = ['#10b981', '#3b82f6', '#f59e0b', '#ef4444', '#8b5cf6'];

export const Dashboard: React.FC<DashboardProps> = ({ indicators, entries }) => {
  const getTrendIcon = (trend: string) => {
    switch (trend) {
      case 'up': return <TrendingUp className="w-4 h-4 text-emerald-500" />;
      case 'down': return <TrendingDown className="w-4 h-4 text-rose-500" />;
      default: return <Minus className="w-4 h-4 text-amber-500" />;
    }
  };

  const getCategoryIcon = (category: string) => {
    switch (category) {
      case 'Outreach': return <Users className="w-5 h-5 text-blue-500" />;
      case 'Capacity Building': return <CheckCircle className="w-5 h-5 text-emerald-500" />;
      case 'Accountability': return <Clock className="w-5 h-5 text-amber-500" />;
      default: return <Target className="w-5 h-5 text-slate-500" />;
    }
  };

  // Prepare data for charts
  const barData = indicators.map(ind => ({
    name: ind.name.length > 20 ? ind.name.substring(0, 20) + '...' : ind.name,
    actual: ind.actual,
    target: ind.target,
    percentage: Math.round((ind.actual / ind.target) * 100)
  }));

  const pieData = indicators.reduce((acc: any[], ind) => {
    const existing = acc.find(a => a.name === ind.category);
    if (existing) {
      existing.value += 1;
    } else {
      acc.push({ name: ind.category, value: 1 });
    }
    return acc;
  }, []);

  // Calculate global disaggregation
  const totalDisaggregation = entries.reduce((acc, entry) => {
    if (entry.disaggregation) {
      acc.male += entry.disaggregation.male || 0;
      acc.female += entry.disaggregation.female || 0;
      acc.youth += entry.disaggregation.youth || 0;
    }
    return acc;
  }, { male: 0, female: 0, youth: 0 });

  return (
    <div className="space-y-8 animate-in fade-in duration-500">
      {/* Top Stats - Bento Style */}
      <div className="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-6">
        {/* Total Reach */}
        <div className="md:col-span-2 lg:col-span-2 bg-indigo-600 p-8 rounded-[2rem] text-white relative overflow-hidden shadow-xl shadow-indigo-500/20">
          <div className="relative z-10">
            <p className="text-indigo-100 text-sm font-bold uppercase tracking-widest mb-2">Total Direct Reach</p>
            <h3 className="text-5xl font-bold mb-6 tracking-tighter">
              {indicators.reduce((sum, i) => sum + i.actual, 0).toLocaleString()}
            </h3>
            <div className="flex gap-4">
              <div className="bg-white/10 backdrop-blur-md px-3 py-1.5 rounded-xl border border-white/10">
                <p className="text-[10px] font-bold uppercase opacity-60">Male</p>
                <p className="text-sm font-bold">{totalDisaggregation.male.toLocaleString()}</p>
              </div>
              <div className="bg-white/10 backdrop-blur-md px-3 py-1.5 rounded-xl border border-white/10">
                <p className="text-[10px] font-bold uppercase opacity-60">Female</p>
                <p className="text-sm font-bold">{totalDisaggregation.female.toLocaleString()}</p>
              </div>
            </div>
          </div>
          <Users className="absolute bottom-[-20px] right-[-20px] w-40 h-40 text-white/10 rotate-12" />
        </div>

        {/* On Track Stats */}
        <div className="bg-white p-8 rounded-[2rem] border border-slate-100 shadow-sm flex flex-col justify-between">
          <div>
            <div className="w-12 h-12 bg-emerald-50 rounded-2xl flex items-center justify-center mb-4">
              <CheckCircle className="w-6 h-6 text-emerald-600" />
            </div>
            <p className="text-slate-500 text-sm font-bold uppercase tracking-widest">On Track</p>
          </div>
          <h3 className="text-4xl font-bold text-slate-900">
            {indicators.filter(i => i.status === 'on-track').length}
          </h3>
        </div>

        {/* At Risk Stats */}
        <div className="bg-white p-8 rounded-[2rem] border border-slate-100 shadow-sm flex flex-col justify-between">
          <div>
            <div className="w-12 h-12 bg-amber-50 rounded-2xl flex items-center justify-center mb-4">
              <AlertCircle className="w-6 h-6 text-amber-600" />
            </div>
            <p className="text-slate-500 text-sm font-bold uppercase tracking-widest">At Risk</p>
          </div>
          <h3 className="text-4xl font-bold text-slate-900">
            {indicators.filter(i => i.status === 'at-risk').length}
          </h3>
        </div>

        {/* Behind Stats */}
        <div className="bg-white p-8 rounded-[2rem] border border-slate-100 shadow-sm flex flex-col justify-between">
          <div>
            <div className="w-12 h-12 bg-rose-50 rounded-2xl flex items-center justify-center mb-4">
              <TrendingDown className="w-6 h-6 text-rose-600" />
            </div>
            <p className="text-slate-500 text-sm font-bold uppercase tracking-widest">Behind</p>
          </div>
          <h3 className="text-4xl font-bold text-slate-900">
            {indicators.filter(i => i.status === 'behind').length}
          </h3>
        </div>

        {/* Total Indicators */}
        <div className="bg-slate-900 p-8 rounded-[2rem] text-white flex flex-col justify-between">
          <div>
            <div className="w-12 h-12 bg-slate-800 rounded-2xl flex items-center justify-center mb-4">
              <Target className="w-6 h-6 text-slate-400" />
            </div>
            <p className="text-slate-400 text-sm font-bold uppercase tracking-widest">Indicators</p>
          </div>
          <h3 className="text-4xl font-bold">
            {indicators.length}
          </h3>
        </div>
      </div>

      {/* Main Charts - Bento Style */}
      <div className="grid grid-cols-1 lg:grid-cols-12 gap-8">
        {/* Performance Chart */}
        <div className="lg:col-span-8 bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100">
          <div className="flex items-center justify-between mb-8">
            <div>
              <h3 className="text-xl font-bold text-slate-900 tracking-tight">Indicator Performance</h3>
              <p className="text-sm text-slate-500">Target vs Actual achievement across indicators</p>
            </div>
          </div>
          <div className="h-[400px] w-full">
            <ResponsiveContainer width="100%" height="100%">
              <BarChart data={barData} margin={{ top: 20, right: 30, left: 20, bottom: 60 }}>
                <CartesianGrid strokeDasharray="3 3" vertical={false} stroke="#f1f5f9" />
                <XAxis 
                  dataKey="name" 
                  axisLine={false} 
                  tickLine={false} 
                  tick={{ fill: '#64748b', fontSize: 11, fontWeight: 600 }}
                  angle={-45}
                  textAnchor="end"
                  interval={0}
                />
                <YAxis axisLine={false} tickLine={false} tick={{ fill: '#64748b', fontSize: 11, fontWeight: 600 }} />
                <Tooltip 
                  cursor={{ fill: '#f8fafc' }}
                  contentStyle={{ borderRadius: '20px', border: 'none', boxShadow: '0 20px 25px -5px rgb(0 0 0 / 0.1)', padding: '16px' }}
                />
                <Legend verticalAlign="top" align="right" iconType="circle" wrapperStyle={{ paddingBottom: '20px' }} />
                <Bar dataKey="actual" fill="#4f46e5" radius={[6, 6, 0, 0]} name="Actual" barSize={32} />
                <Bar dataKey="target" fill="#e2e8f0" radius={[6, 6, 0, 0]} name="Target" barSize={32} />
              </BarChart>
            </ResponsiveContainer>
          </div>
        </div>

        {/* Category Distribution */}
        <div className="lg:col-span-4 bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100">
          <div className="mb-8">
            <h3 className="text-xl font-bold text-slate-900 tracking-tight">Distribution</h3>
            <p className="text-sm text-slate-500">Breakdown by MEAL category</p>
          </div>
          <div className="h-[300px] w-full flex items-center justify-center">
            <ResponsiveContainer width="100%" height="100%">
              <PieChart>
                <Pie
                  data={pieData}
                  cx="50%"
                  cy="50%"
                  innerRadius={70}
                  outerRadius={100}
                  paddingAngle={8}
                  dataKey="value"
                >
                  {pieData.map((entry, index) => (
                    <Cell key={`cell-${index}`} fill={COLORS[index % COLORS.length]} />
                  ))}
                </Pie>
                <Tooltip 
                  contentStyle={{ borderRadius: '20px', border: 'none', boxShadow: '0 20px 25px -5px rgb(0 0 0 / 0.1)' }}
                />
              </PieChart>
            </ResponsiveContainer>
          </div>
          <div className="mt-4 space-y-3">
            {pieData.map((item, idx) => (
              <div key={idx} className="flex items-center justify-between">
                <div className="flex items-center gap-2">
                  <div className="w-3 h-3 rounded-full" style={{ backgroundColor: COLORS[idx % COLORS.length] }} />
                  <span className="text-sm font-medium text-slate-600">{item.name}</span>
                </div>
                <span className="text-sm font-bold text-slate-900">{item.value}</span>
              </div>
            ))}
          </div>
        </div>
      </div>

      {/* Recent Activity & Detailed Stats */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
        {/* Recent Monitoring Entries */}
        <div className="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100">
          <div className="flex items-center justify-between mb-8">
            <h3 className="text-xl font-bold text-slate-900 tracking-tight">Recent Activity</h3>
            <button className="text-xs font-bold text-indigo-600 uppercase tracking-widest hover:text-indigo-700 transition-colors">View All</button>
          </div>
          <div className="space-y-6">
            {entries.slice(0, 5).map((entry, idx) => {
              const indicator = indicators.find(i => i.id === entry.indicatorId);
              return (
                <div key={idx} className="flex items-center gap-4 group">
                  <div className="w-12 h-12 rounded-2xl bg-slate-50 flex items-center justify-center group-hover:bg-indigo-50 transition-colors">
                    <Clock className="w-6 h-6 text-slate-400 group-hover:text-indigo-600 transition-colors" />
                  </div>
                  <div className="flex-1">
                    <p className="text-sm font-bold text-slate-900">{indicator?.name || 'Unknown Indicator'}</p>
                    <p className="text-xs text-slate-500">{entry.location} • {new Date(entry.date).toLocaleDateString()}</p>
                  </div>
                  <div className="text-right">
                    <p className="text-sm font-bold text-indigo-600">+{entry.value}</p>
                    <p className="text-[10px] font-bold text-slate-400 uppercase">{indicator?.unit}</p>
                  </div>
                </div>
              );
            })}
          </div>
        </div>

        {/* Gender Disaggregation Chart */}
        <div className="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100">
          <div className="mb-8">
            <h3 className="text-xl font-bold text-slate-900 tracking-tight">Gender Disaggregation</h3>
            <p className="text-sm text-slate-500">Reach breakdown by gender</p>
          </div>
          <div className="h-[300px] w-full">
            <ResponsiveContainer width="100%" height="100%">
              <BarChart 
                layout="vertical" 
                data={[
                  { name: 'Male', value: totalDisaggregation.male, color: '#3b82f6' },
                  { name: 'Female', value: totalDisaggregation.female, color: '#ec4899' },
                  { name: 'Youth', value: totalDisaggregation.youth, color: '#8b5cf6' }
                ]}
                margin={{ left: 20, right: 40 }}
              >
                <XAxis type="number" hide />
                <YAxis 
                  dataKey="name" 
                  type="category" 
                  axisLine={false} 
                  tickLine={false} 
                  tick={{ fill: '#64748b', fontSize: 12, fontWeight: 600 }}
                />
                <Tooltip 
                  cursor={{ fill: 'transparent' }}
                  contentStyle={{ borderRadius: '16px', border: 'none', boxShadow: '0 10px 15px -3px rgb(0 0 0 / 0.1)' }}
                />
                <Bar dataKey="value" radius={[0, 8, 8, 0]} barSize={40}>
                  {
                    [
                      { name: 'Male', value: totalDisaggregation.male, color: '#3b82f6' },
                      { name: 'Female', value: totalDisaggregation.female, color: '#ec4899' },
                      { name: 'Youth', value: totalDisaggregation.youth, color: '#8b5cf6' }
                    ].map((entry, index) => (
                      <Cell key={`cell-${index}`} fill={entry.color} />
                    ))
                  }
                </Bar>
              </BarChart>
            </ResponsiveContainer>
          </div>
        </div>
      </div>
    </div>
  );
};
