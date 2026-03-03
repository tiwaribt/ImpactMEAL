import React from 'react';
import { 
  BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer,
  LineChart, Line, PieChart, Pie, Cell
} from 'recharts';
import { Indicator, MonitoringEntry } from '../types';
import { TrendingUp, TrendingDown, Minus, Target, Users, CheckCircle, Clock } from 'lucide-react';

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

  return (
    <div className="space-y-8 animate-in fade-in duration-500">
      {/* Stat Cards */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        {indicators.map((ind) => (
          <div key={ind.id} className="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition-shadow">
            <div className="flex items-center justify-between mb-4">
              <div className="p-2 bg-slate-50 rounded-lg">
                {getCategoryIcon(ind.category)}
              </div>
              <div className="flex items-center gap-1 text-xs font-medium px-2 py-1 bg-slate-50 rounded-full">
                {getTrendIcon(ind.trend)}
                <span className="capitalize">{ind.trend}</span>
              </div>
            </div>
            <h3 className="text-sm font-medium text-slate-500 mb-1">{ind.name}</h3>
            <div className="flex items-baseline gap-2">
              <span className="text-2xl font-bold text-slate-900">{ind.actual}</span>
              <span className="text-sm text-slate-400">/ {ind.target} {ind.unit}</span>
            </div>
            <div className="mt-4 w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
              <div 
                className={`h-full rounded-full transition-all duration-1000 ${
                  (ind.actual / ind.target) >= 0.9 ? 'bg-emerald-500' : 
                  (ind.actual / ind.target) >= 0.7 ? 'bg-amber-500' : 'bg-rose-500'
                }`}
                style={{ width: `${Math.min((ind.actual / ind.target) * 100, 100)}%` }}
              />
            </div>
          </div>
        ))}
      </div>

      {/* Charts Section */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
        {/* Performance Chart */}
        <div className="bg-white p-8 rounded-2xl shadow-sm border border-slate-100">
          <div className="flex items-center justify-between mb-8">
            <div>
              <h3 className="text-lg font-semibold text-slate-900">Indicator Performance</h3>
              <p className="text-sm text-slate-500">Target vs Actual achievement across indicators</p>
            </div>
          </div>
          <div className="h-[350px] w-full">
            <ResponsiveContainer width="100%" height="100%">
              <BarChart data={barData} margin={{ top: 20, right: 30, left: 20, bottom: 60 }}>
                <CartesianGrid strokeDasharray="3 3" vertical={false} stroke="#f1f5f9" />
                <XAxis 
                  dataKey="name" 
                  axisLine={false} 
                  tickLine={false} 
                  tick={{ fill: '#64748b', fontSize: 12 }}
                  angle={-45}
                  textAnchor="end"
                  interval={0}
                />
                <YAxis axisLine={false} tickLine={false} tick={{ fill: '#64748b', fontSize: 12 }} />
                <Tooltip 
                  cursor={{ fill: '#f8fafc' }}
                  contentStyle={{ borderRadius: '12px', border: 'none', boxShadow: '0 10px 15px -3px rgb(0 0 0 / 0.1)' }}
                />
                <Legend verticalAlign="top" align="right" iconType="circle" />
                <Bar dataKey="actual" fill="#3b82f6" radius={[4, 4, 0, 0]} name="Actual" />
                <Bar dataKey="target" fill="#e2e8f0" radius={[4, 4, 0, 0]} name="Target" />
              </BarChart>
            </ResponsiveContainer>
          </div>
        </div>

        {/* Category Distribution */}
        <div className="bg-white p-8 rounded-2xl shadow-sm border border-slate-100">
          <div className="flex items-center justify-between mb-8">
            <div>
              <h3 className="text-lg font-semibold text-slate-900">Indicator Distribution</h3>
              <p className="text-sm text-slate-500">Breakdown of indicators by MEAL category</p>
            </div>
          </div>
          <div className="h-[350px] w-full flex items-center justify-center">
            <ResponsiveContainer width="100%" height="100%">
              <PieChart>
                <Pie
                  data={pieData}
                  cx="50%"
                  cy="50%"
                  innerRadius={80}
                  outerRadius={120}
                  paddingAngle={5}
                  dataKey="value"
                >
                  {pieData.map((entry, index) => (
                    <Cell key={`cell-${index}`} fill={COLORS[index % COLORS.length]} />
                  ))}
                </Pie>
                <Tooltip 
                  contentStyle={{ borderRadius: '12px', border: 'none', boxShadow: '0 10px 15px -3px rgb(0 0 0 / 0.1)' }}
                />
                <Legend verticalAlign="bottom" iconType="circle" />
              </PieChart>
            </ResponsiveContainer>
          </div>
        </div>
      </div>
    </div>
  );
};
