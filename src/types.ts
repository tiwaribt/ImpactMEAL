export enum IndicatorType {
  QUANTITATIVE = 'QUANTITATIVE',
  QUALITATIVE = 'QUALITATIVE',
}

export interface Indicator {
  id: string;
  projectId?: string;
  name: string;
  target: number;
  actual: number;
  unit: string;
  category: string;
  trend: 'up' | 'down' | 'stable';
  status: 'on-track' | 'at-risk' | 'behind';
  gap: number;
  achievedPercentage: number;
  lastUpdated: string;
  geojson?: any; // For GIS reporting
  disaggregation?: {
    male?: number;
    female?: number;
    youth?: number;
    other?: number;
  };
}

export interface MonitoringEntry {
  id: string;
  date: string;
  indicatorId: string;
  value: number;
  location: string;
  notes?: string;
  coordinates?: [number, number]; // [lat, lng]
  disaggregation?: {
    male?: number;
    female?: number;
    youth?: number;
    other?: number;
  };
}

export interface User {
  id: string;
  username: string;
  email: string;
  role: 'admin' | 'meal_officer' | 'viewer';
}

export interface QualitativeFeedback {
  id: string;
  date: string;
  source: string;
  content: string;
  sentiment?: 'positive' | 'neutral' | 'negative';
  themes?: string[];
  summary?: string;
}

export interface GISFeature {
  id: string;
  name: string;
  type: 'Feature';
  geometry: {
    type: 'Point' | 'Polygon' | 'MultiPolygon';
    coordinates: any;
  };
  properties: {
    indicatorValue: number;
    locationName: string;
    status: string;
  };
}
