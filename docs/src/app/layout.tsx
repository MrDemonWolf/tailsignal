import './global.css';
import { RootProvider } from 'fumadocs-ui/provider';
import type { Metadata } from 'next';
import type { ReactNode } from 'react';
import { WipBanner } from '@/components/wip-banner';

export const metadata: Metadata = {
    title: {
        template: '%s | TailSignal Docs',
        default: 'TailSignal Docs',
    },
    description:
        'Documentation for TailSignal — a self-hosted WordPress plugin for push notifications via Expo.',
};

export default function RootLayout({ children }: { children: ReactNode }) {
    return (
        <html lang="en" suppressHydrationWarning>
            <body>
                <RootProvider>
                    <WipBanner />
                    {children}
                </RootProvider>
            </body>
        </html>
    );
}
