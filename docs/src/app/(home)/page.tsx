import Link from 'next/link';

const features = [
    {
        title: 'Push Notifications',
        description:
            'Send rich push notifications to iOS and Android devices with images, custom data, and scheduling.',
        icon: '🔔',
    },
    {
        title: 'Self-Hosted',
        description:
            'Your data stays on your server. No third-party services, no monthly fees, full control.',
        icon: '🏠',
    },
    {
        title: 'Expo / React Native',
        description:
            'Works with any Expo or React Native app using Expo Push Tokens. Simple REST API integration.',
        icon: '📱',
    },
    {
        title: 'Auto-Notify',
        description:
            'Automatically send notifications when new posts or portfolio items are published.',
        icon: '⚡',
    },
];

export default function HomePage() {
    return (
        <main className="flex min-h-screen flex-col items-center px-4 py-16">
            <div className="mx-auto max-w-4xl text-center">
                <h1 className="mb-4 text-5xl font-bold tracking-tight">
                    TailSignal
                </h1>
                <p className="mb-8 text-lg text-fd-muted-foreground">
                    A self-hosted WordPress plugin that sends push notifications
                    to mobile devices via the Expo Push Service. Own your data,
                    bypass OneSignal, and keep your pack in the loop.
                </p>
                <div className="flex flex-wrap justify-center gap-4">
                    <Link
                        href="https://github.com/MrDemonWolf/tailsignal/releases/latest"
                        className="inline-flex items-center gap-2 rounded-lg bg-[#0FACED] px-6 py-3 font-semibold text-white transition-colors hover:bg-[#0991d4]"
                    >
                        Download Latest Release
                    </Link>
                    <Link
                        href="/docs"
                        className="inline-flex items-center gap-2 rounded-lg border border-fd-border px-6 py-3 font-semibold transition-colors hover:bg-fd-accent"
                    >
                        Read the Docs
                    </Link>
                </div>
            </div>

            <div className="mx-auto mt-20 grid max-w-4xl gap-6 sm:grid-cols-2">
                {features.map((feature) => (
                    <div
                        key={feature.title}
                        className="rounded-xl border border-fd-border bg-fd-card p-6"
                    >
                        <div className="mb-3 text-3xl">{feature.icon}</div>
                        <h3 className="mb-2 text-lg font-semibold">
                            {feature.title}
                        </h3>
                        <p className="text-sm text-fd-muted-foreground">
                            {feature.description}
                        </p>
                    </div>
                ))}
            </div>

            <footer className="mt-20 text-center text-sm text-fd-muted-foreground">
                <p>
                    Built by{' '}
                    <a
                        href="https://github.com/mrdemonwolf"
                        className="underline"
                    >
                        MrDemonWolf, Inc.
                    </a>{' '}
                    &middot; Licensed under GPL-2.0+
                </p>
            </footer>
        </main>
    );
}
