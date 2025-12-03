import React, { useState } from 'react';
import { Shield, Smartphone, Mail, Database, Key, CheckCircle } from 'lucide-react';

export default function TOTPArchitectureDiagram() {
  const [activeStep, setActiveStep] = useState(null);

  const steps = [
    {
      id: 1,
      title: "Email Login με Aliases",
      description: "Ο χρήστης μπορεί να κάνει login με οποιοδήποτε alias",
      icon: Mail,
      details: [
        "username1@domain1.com",
        "username1@alias1.com", 
        "username1@alias2.com"
      ],
      color: "bg-blue-500"
    },
    {
      id: 2,
      title: "Username Extraction",
      description: "Εξαγωγή του username χωρίς το domain",
      icon: Key,
      details: [
        "Input: username1@domain1.com",
        "Extract: username1",
        "Normalize: lowercase"
      ],
      color: "bg-purple-500"
    },
    {
      id: 3,
      title: "Database Lookup",
      description: "Αναζήτηση TOTP secret με βάση το username",
      icon: Database,
      details: [
        "Key: 'username1'",
        "Secret: Base32 encoded",
        "Status: enabled/disabled"
      ],
      color: "bg-green-500"
    },
    {
      id: 4,
      title: "TOTP Generation",
      description: "Υπολογισμός του 6-ψήφιου κωδικού",
      icon: Smartphone,
      details: [
        "Algorithm: HMAC-SHA1",
        "Time step: 30 seconds",
        "Code length: 6 digits"
      ],
      color: "bg-orange-500"
    },
    {
      id: 5,
      title: "Verification",
      description: "Επαλήθευση του κωδικού",
      icon: CheckCircle,
      details: [
        "User enters code",
        "Compare with generated",
        "Allow ±30s tolerance"
      ],
      color: "bg-red-500"
    }
  ];

  return (
    <div className="w-full max-w-6xl mx-auto p-6 bg-gradient-to-br from-gray-50 to-gray-100">
      <div className="bg-white rounded-xl shadow-2xl p-8">
        <div className="flex items-center gap-3 mb-8">
          <Shield className="w-10 h-10 text-blue-600" />
          <h1 className="text-3xl font-bold text-gray-800">
            2FA Architecture με Unified TOTP
          </h1>
        </div>

        <div className="mb-8 p-6 bg-blue-50 rounded-lg border-l-4 border-blue-500">
          <h2 className="text-xl font-semibold text-blue-900 mb-3">
            Βασική Ιδέα
          </h2>
          <p className="text-gray-700 leading-relaxed">
            Όλα τα email aliases που έχουν το <strong>ίδιο username</strong> (το μέρος πριν το @) 
            μοιράζονται το <strong>ίδιο TOTP secret</strong>. Αυτό σημαίνει ότι ο χρήστης χρειάζεται 
            μόνο <strong>ένα QR code</strong> στο authenticator app του για όλα τα aliases!
          </p>
        </div>

        {/* Flow Diagram */}
        <div className="grid grid-cols-1 md:grid-cols-5 gap-4 mb-8">
          {steps.map((step, index) => {
            const Icon = step.icon;
            const isActive = activeStep === step.id;
            
            return (
              <div key={step.id} className="relative">
                <div
                  className={`
                    cursor-pointer transition-all duration-300 transform
                    ${isActive ? 'scale-105' : 'hover:scale-102'}
                  `}
                  onClick={() => setActiveStep(isActive ? null : step.id)}
                >
                  <div className={`
                    ${step.color} text-white rounded-lg p-4 shadow-lg
                    ${isActive ? 'ring-4 ring-offset-2 ring-blue-400' : ''}
                  `}>
                    <Icon className="w-12 h-12 mx-auto mb-3" />
                    <h3 className="font-bold text-center text-sm mb-1">
                      {step.title}
                    </h3>
                    <p className="text-xs text-center opacity-90">
                      {step.description}
                    </p>
                  </div>
                </div>
                
                {index < steps.length - 1 && (
                  <div className="hidden md:block absolute top-1/2 -right-2 transform -translate-y-1/2 z-10">
                    <div className="text-3xl text-gray-400">→</div>
                  </div>
                )}
              </div>
            );
          })}
        </div>

        {/* Details Panel */}
        {activeStep && (
          <div className="mt-6 p-6 bg-gradient-to-r from-indigo-50 to-purple-50 rounded-lg border border-indigo-200 animate-fadeIn">
            <h3 className="text-xl font-bold text-indigo-900 mb-4">
              {steps.find(s => s.id === activeStep).title}
            </h3>
            <ul className="space-y-2">
              {steps.find(s => s.id === activeStep).details.map((detail, idx) => (
                <li key={idx} className="flex items-start gap-2">
                  <span className="text-indigo-500 mt-1">▸</span>
                  <span className="text-gray-700 font-mono text-sm">{detail}</span>
                </li>
              ))}
            </ul>
          </div>
        )}

        {/* Example Scenario */}
        <div className="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6">
          <div className="bg-green-50 p-6 rounded-lg border border-green-200">
            <h3 className="text-lg font-bold text-green-900 mb-4 flex items-center gap-2">
              <Mail className="w-5 h-5" />
              Παράδειγμα Χρήσης
            </h3>
            <div className="space-y-3 text-sm">
              <div className="bg-white p-3 rounded">
                <strong className="text-green-700">Βήμα 1:</strong>
                <p className="text-gray-600 mt-1">
                  Ο χρήστης ενεργοποιεί 2FA από το username1@domain1.com
                </p>
              </div>
              <div className="bg-white p-3 rounded">
                <strong className="text-green-700">Βήμα 2:</strong>
                <p className="text-gray-600 mt-1">
                  Το σύστημα δημιουργεί secret για το "username1"
                </p>
              </div>
              <div className="bg-white p-3 rounded">
                <strong className="text-green-700">Βήμα 3:</strong>
                <p className="text-gray-600 mt-1">
                  Ο χρήστης σκανάρει το QR code με το authenticator app
                </p>
              </div>
              <div className="bg-white p-3 rounded">
                <strong className="text-green-700">Βήμα 4:</strong>
                <p className="text-gray-600 mt-1">
                  Τώρα μπορεί να κάνει login με ΟΠΟΙΟΔΗΠΟΤΕ alias (domain1.com, alias1.com, alias2.com) 
                  χρησιμοποιώντας το ΙΔΙΟ OTP code!
                </p>
              </div>
            </div>
          </div>

          <div className="bg-purple-50 p-6 rounded-lg border border-purple-200">
            <h3 className="text-lg font-bold text-purple-900 mb-4 flex items-center gap-2">
              <Database className="w-5 h-5" />
              Database Schema
            </h3>
            <div className="bg-white p-4 rounded font-mono text-xs overflow-x-auto">
              <pre className="text-gray-700">{`
CREATE TABLE totp_secrets (
  id INT AUTO_INCREMENT,
  username VARCHAR(255) UNIQUE,
  secret VARCHAR(64),
  enabled TINYINT(1),
  created_at TIMESTAMP,
  PRIMARY KEY (id),
  INDEX idx_username (username)
);

-- Παράδειγμα δεδομένων:
-- username  | secret        | enabled
-- ----------|---------------|--------
-- username1 | ABC...XYZ234  | 1
-- username2 | DEF...UVW567  | 1
              `}</pre>
            </div>
          </div>
        </div>

        {/* TOTP Algorithm Visualization */}
        <div className="mt-8 bg-gradient-to-r from-blue-50 to-indigo-50 p-6 rounded-lg border border-blue-200">
          <h3 className="text-lg font-bold text-blue-900 mb-4">
            TOTP Algorithm Flow
          </h3>
          <div className="flex flex-col md:flex-row items-center justify-between gap-4 text-sm">
            <div className="bg-white p-4 rounded shadow text-center flex-1">
              <div className="font-bold text-blue-700 mb-2">Secret Key</div>
              <div className="font-mono text-xs text-gray-600">Base32 Encoded</div>
            </div>
            <div className="text-2xl text-blue-400">+</div>
            <div className="bg-white p-4 rounded shadow text-center flex-1">
              <div className="font-bold text-blue-700 mb-2">Current Time</div>
              <div className="font-mono text-xs text-gray-600">floor(time/30)</div>
            </div>
            <div className="text-2xl text-blue-400">→</div>
            <div className="bg-white p-4 rounded shadow text-center flex-1">
              <div className="font-bold text-blue-700 mb-2">HMAC-SHA1</div>
              <div className="font-mono text-xs text-gray-600">Hash Function</div>
            </div>
            <div className="text-2xl text-blue-400">→</div>
            <div className="bg-green-100 p-4 rounded shadow text-center flex-1 border-2 border-green-400">
              <div className="font-bold text-green-700 mb-2">6-Digit OTP</div>
              <div className="font-mono text-2xl text-green-600">123456</div>
            </div>
          </div>
        </div>

        {/* Implementation Notes */}
        <div className="mt-8 bg-yellow-50 p-6 rounded-lg border-l-4 border-yellow-400">
          <h3 className="text-lg font-bold text-yellow-900 mb-3">
            ⚠️ Σημαντικές Σημειώσεις Υλοποίησης
          </h3>
          <ul className="space-y-2 text-sm text-gray-700">
            <li className="flex items-start gap-2">
              <span className="text-yellow-600 mt-1">•</span>
              <span>Το username πρέπει να κανονικοποιείται (lowercase) για συνέπεια</span>
            </li>
            <li className="flex items-start gap-2">
              <span className="text-yellow-600 mt-1">•</span>
              <span>Χρησιμοποιούμε time tolerance (±1 step) για clock skew</span>
            </li>
            <li className="flex items-start gap-2">
              <span className="text-yellow-600 mt-1">•</span>
              <span>Το secret πρέπει να αποθηκεύεται με ασφάλεια (encrypted στη βάση)</span>
            </li>
            <li className="flex items-start gap-2">
              <span className="text-yellow-600 mt-1">•</span>
              <span>Υποστηρίζουμε Google Authenticator, Microsoft Authenticator, Authy, κλπ.</span>
            </li>
          </ul>
        </div>
      </div>

      <style jsx>{`
        @keyframes fadeIn {
          from {
            opacity: 0;
            transform: translateY(-10px);
          }
          to {
            opacity: 1;
            transform: translateY(0);
          }
        }
        .animate-fadeIn {
          animation: fadeIn 0.3s ease-out;
        }
      `}</style>
    </div>
  );
}