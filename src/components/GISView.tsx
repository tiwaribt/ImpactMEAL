import React, { useState } from 'react';
import { MapContainer, TileLayer, GeoJSON, Marker, Popup } from 'react-leaflet';
import 'leaflet/dist/leaflet.css';
import L from 'leaflet';
import { Indicator, MonitoringEntry } from '../types';
import { Map as MapIcon, Layers, Info, Upload, FileJson } from 'lucide-react';

// Fix for default marker icon in Leaflet + React
// @ts-ignore
delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
  iconRetinaUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-icon-2x.png',
  iconUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-icon.png',
  shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png',
});

interface GISViewProps {
  indicators: Indicator[];
  entries: MonitoringEntry[];
}

export const GISView: React.FC<GISViewProps> = ({ indicators, entries }) => {
  const [geoJsonData, setGeoJsonData] = useState<any>(null);

  const handleGeoJsonUpload = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = (event) => {
      try {
        const json = JSON.parse(event.target?.result as string);
        setGeoJsonData(json);
      } catch (error) {
        console.error("Invalid GeoJSON file:", error);
        alert("Invalid GeoJSON file. Please check the format.");
      }
    };
    reader.readAsText(file);
  };

  // Default center (e.g., Addis Ababa for Dereja context)
  const center: [number, number] = [9.03, 38.74];

  return (
    <div className="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-500">
      <div className="flex flex-col sm:flex-row items-center justify-between gap-4">
        <div>
          <h3 className="text-lg font-bold text-slate-900">GIS Reporting & Mapping</h3>
          <p className="text-sm text-slate-500">Visualize program impact and indicator status across geographical regions.</p>
        </div>
        <div className="flex items-center gap-3">
          <label className="flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-50 transition-colors cursor-pointer">
            <Upload className="w-4 h-4" />
            Upload GeoJSON
            <input 
              type="file" 
              accept=".json,.geojson" 
              className="hidden" 
              onChange={handleGeoJsonUpload}
            />
          </label>
        </div>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <div className="lg:col-span-3 bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden h-[600px] relative">
          <MapContainer 
            center={center} 
            zoom={6} 
            style={{ height: '100%', width: '100%' }}
            scrollWheelZoom={false}
          >
            <TileLayer
              attribution='&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
              url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
            />
            {geoJsonData && (
              <GeoJSON 
                data={geoJsonData} 
                style={() => ({
                  fillColor: '#6366f1',
                  weight: 2,
                  opacity: 1,
                  color: 'white',
                  fillOpacity: 0.3
                })}
              />
            )}
            {entries.filter(e => e.coordinates).map(entry => (
              <Marker key={entry.id} position={entry.coordinates!}>
                <Popup>
                  <div className="p-2">
                    <h4 className="font-bold text-slate-900">{entry.location}</h4>
                    <p className="text-xs text-slate-500 mb-2">{entry.date}</p>
                    <div className="p-2 bg-indigo-50 rounded-lg border border-indigo-100">
                      <p className="text-[10px] font-bold text-indigo-600 uppercase tracking-wider">Value</p>
                      <p className="text-sm font-bold text-indigo-900">{entry.value}</p>
                    </div>
                  </div>
                </Popup>
              </Marker>
            ))}
          </MapContainer>
          
          <div className="absolute top-4 right-4 z-[1000] bg-white/90 backdrop-blur-sm p-4 rounded-xl shadow-lg border border-slate-200 w-48">
            <h4 className="text-xs font-bold text-slate-900 uppercase tracking-wider mb-3 flex items-center gap-2">
              <Layers className="w-3.5 h-3.5 text-indigo-500" />
              Map Legend
            </h4>
            <div className="space-y-2">
              <div className="flex items-center gap-2">
                <div className="w-3 h-3 rounded-full bg-emerald-500"></div>
                <span className="text-[10px] font-medium text-slate-600">On Track</span>
              </div>
              <div className="flex items-center gap-2">
                <div className="w-3 h-3 rounded-full bg-amber-500"></div>
                <span className="text-[10px] font-medium text-slate-600">At Risk</span>
              </div>
              <div className="flex items-center gap-2">
                <div className="w-3 h-3 rounded-full bg-rose-500"></div>
                <span className="text-[10px] font-medium text-slate-600">Behind</span>
              </div>
            </div>
          </div>
        </div>

        <div className="space-y-6">
          <div className="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
            <h4 className="text-sm font-bold text-slate-900 mb-4 flex items-center gap-2">
              <Info className="w-4 h-4 text-indigo-500" />
              GIS Insights
            </h4>
            <div className="space-y-4">
              <div className="p-4 bg-slate-50 rounded-xl border border-slate-100">
                <p className="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Spatial Coverage</p>
                <p className="text-lg font-bold text-slate-900">12 Regions</p>
                <p className="text-[10px] text-slate-500 mt-1">Active monitoring in 85% of target areas.</p>
              </div>
              <div className="p-4 bg-slate-50 rounded-xl border border-slate-100">
                <p className="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Hotspots</p>
                <p className="text-lg font-bold text-rose-600">3 Regions</p>
                <p className="text-[10px] text-slate-500 mt-1">Identified as "Behind" status in the last 30 days.</p>
              </div>
            </div>
          </div>

          <div className="bg-indigo-600 p-6 rounded-2xl shadow-lg shadow-indigo-600/20 text-white">
            <h4 className="text-sm font-bold mb-2 flex items-center gap-2">
              <FileJson className="w-4 h-4" />
              GeoJSON Support
            </h4>
            <p className="text-xs text-indigo-100 leading-relaxed mb-4">
              Upload your project's administrative boundaries or specific site locations in GeoJSON format to overlay monitoring data.
            </p>
            <button className="w-full py-2 bg-white/10 hover:bg-white/20 rounded-xl text-xs font-bold transition-all border border-white/20">
              Download Template
            </button>
          </div>
        </div>
      </div>
    </div>
  );
};
