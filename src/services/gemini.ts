import { GoogleGenAI, Type } from "@google/genai";
import { QualitativeFeedback } from "../types";

const ai = new GoogleGenAI({ apiKey: process.env.GEMINI_API_KEY! });

export async function analyzeFeedback(content: string): Promise<Partial<QualitativeFeedback>> {
  const response = await ai.models.generateContent({
    model: "gemini-3-flash-preview",
    contents: `Analyze the following qualitative feedback from a MEAL (Monitoring, Evaluation, Accountability, and Learning) perspective.
    Extract the sentiment, key themes, and a concise summary.
    
    Feedback: "${content}"`,
    config: {
      responseMimeType: "application/json",
      responseSchema: {
        type: Type.OBJECT,
        properties: {
          sentiment: {
            type: Type.STRING,
            description: "One of 'positive', 'neutral', 'negative'",
          },
          themes: {
            type: Type.ARRAY,
            items: { type: Type.STRING },
            description: "List of key themes mentioned in the feedback",
          },
          summary: {
            type: Type.STRING,
            description: "A one-sentence summary of the feedback",
          },
        },
        required: ["sentiment", "themes", "summary"],
      },
    },
  });

  try {
    return JSON.parse(response.text || "{}");
  } catch (error) {
    console.error("Error parsing Gemini response:", error);
    return {};
  }
}

export async function generateMEALReport(
  indicators: any[],
  feedback: QualitativeFeedback[],
  period: string
): Promise<string> {
  const response = await ai.models.generateContent({
    model: "gemini-3-flash-preview",
    contents: `Generate a comprehensive MEAL (Monitoring, Evaluation, Accountability, and Learning) report for the period: ${period}.
    
    Quantitative Data (Indicators):
    ${JSON.stringify(indicators, null, 2)}
    
    Qualitative Data (Feedback):
    ${JSON.stringify(feedback, null, 2)}
    
    The report should include:
    1. Executive Summary
    2. Quantitative Performance Analysis (Target vs Actual)
    3. Qualitative Insights and Themes
    4. Accountability and Learning (Successes and Challenges)
    5. Recommendations for the next period
    
    Format the report in Markdown.`,
  });

  return response.text || "Failed to generate report.";
}
