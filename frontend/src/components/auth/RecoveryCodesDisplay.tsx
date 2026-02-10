import { useState } from "react";
import { Button } from "@/components/ui/button";

interface Props {
  codes: string[];
  onComplete: () => void;
}

export function RecoveryCodesDisplay({ codes, onComplete }: Props) {
  const [confirmed, setConfirmed] = useState(false);

  const handleCopy = () => {
    navigator.clipboard.writeText(codes.join("\n"));
  };

  const handleDownload = () => {
    const content = `RoomDash Recovery Codes\n${"=".repeat(30)}\n\nStore these codes in a safe place.\nEach code can only be used once.\n\n${codes.join("\n")}`;
    const blob = new Blob([content], { type: "text/plain" });
    const url = URL.createObjectURL(blob);
    const a = document.createElement("a");
    a.href = url;
    a.download = "roomdash-recovery-codes.txt";
    a.click();
    URL.revokeObjectURL(url);
  };

  return (
    <div className="w-full max-w-md mx-auto">
      <div className="text-center mb-6">
        <div className="inline-flex items-center justify-center w-12 h-12 rounded-full bg-green-100 dark:bg-green-900 mb-4">
          <svg
            className="w-6 h-6 text-green-600 dark:text-green-400"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              strokeLinecap="round"
              strokeLinejoin="round"
              strokeWidth={2}
              d="M5 13l4 4L19 7"
            />
          </svg>
        </div>
        <h1 className="text-2xl font-bold">Save Your Recovery Codes</h1>
        <p className="text-muted-foreground mt-2">
          Store these codes in a secure location. You can use them to access your account if you
          lose your authenticator device.
        </p>
      </div>

      <div className="bg-muted p-4 rounded-lg mb-4">
        <div className="grid grid-cols-2 gap-2 font-mono text-sm">
          {codes.map((code, index) => (
            <div key={index} className="p-2 bg-background rounded text-center">
              {code}
            </div>
          ))}
        </div>
      </div>

      <div className="flex gap-2 mb-6">
        <Button variant="outline" className="flex-1" onClick={handleCopy}>
          <svg
            className="w-4 h-4 mr-2"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              strokeLinecap="round"
              strokeLinejoin="round"
              strokeWidth={2}
              d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"
            />
          </svg>
          Copy
        </Button>
        <Button variant="outline" className="flex-1" onClick={handleDownload}>
          <svg
            className="w-4 h-4 mr-2"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              strokeLinecap="round"
              strokeLinejoin="round"
              strokeWidth={2}
              d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"
            />
          </svg>
          Download
        </Button>
      </div>

      <div className="space-y-4">
        <label className="flex items-start gap-3 p-3 border rounded-lg cursor-pointer hover:bg-muted/50">
          <input
            type="checkbox"
            checked={confirmed}
            onChange={(e) => setConfirmed(e.target.checked)}
            className="mt-0.5"
          />
          <span className="text-sm">
            I have saved these recovery codes in a secure location
          </span>
        </label>

        <Button className="w-full" disabled={!confirmed} onClick={onComplete}>
          Continue to Dashboard
        </Button>
      </div>

      <p className="text-xs text-muted-foreground text-center mt-4">
        You won't be able to see these codes again. Make sure to save them now.
      </p>
    </div>
  );
}
