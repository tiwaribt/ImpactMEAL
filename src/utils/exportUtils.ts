import { jsPDF } from 'jspdf';
import 'jspdf-autotable';
import * as XLSX from 'xlsx';
import html2canvas from 'html2canvas';
import { Indicator, MonitoringEntry, QualitativeFeedback } from '../types';

export const exportToExcel = (data: any[], fileName: string) => {
  const ws = XLSX.utils.json_to_sheet(data);
  const wb = XLSX.utils.book_new();
  XLSX.utils.book_append_sheet(wb, ws, 'Sheet1');
  XLSX.writeFile(wb, `${fileName}.xlsx`);
};

export const exportIndicatorsToPDF = (indicators: Indicator[]) => {
  const doc = new jsPDF();
  doc.setFontSize(18);
  doc.text('Indicator Status Report', 14, 22);
  doc.setFontSize(11);
  doc.setTextColor(100);
  doc.text(`Generated on: ${new Date().toLocaleDateString()}`, 14, 30);

  const tableData = indicators.map(ind => [
    ind.name,
    ind.category,
    `${ind.actual} / ${ind.target} ${ind.unit}`,
    `${ind.achievedPercentage}%`,
    ind.status.toUpperCase()
  ]);

  (doc as any).autoTable({
    startY: 40,
    head: [['Indicator', 'Category', 'Actual/Target', 'Achievement', 'Status']],
    body: tableData,
    theme: 'grid',
    headStyles: { fillColor: [79, 70, 229] }
  });

  doc.save('indicator-status-report.pdf');
};

export const generateInfographicPDF = async (elementId: string, title: string) => {
  const element = document.getElementById(elementId);
  if (!element) return;

  const canvas = await html2canvas(element, {
    scale: 2,
    useCORS: true,
    logging: false
  });
  
  const imgData = canvas.toDataURL('image/png');
  const pdf = new jsPDF('p', 'mm', 'a4');
  const imgProps = pdf.getImageProperties(imgData);
  const pdfWidth = pdf.internal.pageSize.getWidth();
  const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;
  
  pdf.setFontSize(16);
  pdf.text(title, 14, 15);
  pdf.addImage(imgData, 'PNG', 0, 20, pdfWidth, pdfHeight);
  pdf.save(`${title.toLowerCase().replace(/\s+/g, '-')}.pdf`);
};
