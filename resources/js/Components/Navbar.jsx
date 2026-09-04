import ApplicationLogo from '@/Components/ApplicationLogo';
import { Link, usePage } from '@inertiajs/react';
import { useState } from 'react';

export default function Navbar() {
    const user = usePage().props.auth.user;
    const [open, setOpen] = useState(false);

    const navLinks = [
        { name: 'Home', href: '/' },
        { name: 'Templates', href: '/templates' },
    ];

    return (
        <nav className="border-b border-gray-200 bg-white">
            <div className="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                <div className="flex items-center gap-6">
                    <Link href="/">
                        <ApplicationLogo className="block h-9 w-auto fill-current text-gray-800" />
                    </Link>

                    <div className="hidden items-center gap-1 md:flex">
                        {navLinks.map((link) => (
                            <Link
                                key={link.name}
                                href={link.href}
                                className="rounded-md px-3 py-2 text-sm font-medium text-gray-600 transition hover:bg-gray-100 hover:text-gray-900"
                            >
                                {link.name}
                            </Link>
                        ))}
                    </div>
                </div>

                <div className="hidden items-center gap-3 md:flex">
                    {user ? (
                        <>
                            <Link
                                href={route('dashboard')}
                                className="inline-flex items-center justify-center rounded-lg px-2.5 py-2 text-sm font-medium transition hover:bg-muted hover:text-foreground"
                            >
                                Dashboard
                            </Link>
                            <Link
                                href={route('logout')}
                                method="post"
                                as="button"
                                className="inline-flex items-center justify-center rounded-lg border border-border bg-background px-2.5 py-2 text-sm font-medium transition hover:bg-muted hover:text-foreground"
                            >
                                Log Out
                            </Link>
                        </>
                    ) : (
                        <>
                            <Link
                                href={route('login')}
                                className="inline-flex items-center justify-center rounded-lg px-2.5 py-2 text-sm font-medium transition hover:bg-muted hover:text-foreground"
                            >
                                Log in
                            </Link>
                            <Link
                                href={route('register')}
                                className="inline-flex items-center justify-center rounded-lg bg-primary px-2.5 py-2 text-sm font-medium text-primary-foreground transition hover:bg-primary/80"
                            >
                                Sign Up
                            </Link>
                        </>
                    )}
                </div>

                <button
                    onClick={() => setOpen((prev) => !prev)}
                    className="inline-flex items-center justify-center rounded-md p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-500 md:hidden"
                >
                    <svg
                        className="h-6 w-6"
                        stroke="currentColor"
                        fill="none"
                        viewBox="0 0 24 24"
                    >
                        <path
                            className={!open ? 'inline-flex' : 'hidden'}
                            strokeLinecap="round"
                            strokeLinejoin="round"
                            strokeWidth="2"
                            d="M4 6h16M4 12h16M4 18h16"
                        />
                        <path
                            className={open ? 'inline-flex' : 'hidden'}
                            strokeLinecap="round"
                            strokeLinejoin="round"
                            strokeWidth="2"
                            d="M6 18L18 6M6 6l12 12"
                        />
                    </svg>
                </button>
            </div>

            {open && (
                <div className="space-y-1 border-t border-gray-200 px-4 pb-3 pt-2 md:hidden">
                    {navLinks.map((link) => (
                        <Link
                            key={link.name}
                            href={link.href}
                            className="block rounded-md px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900"
                        >
                            {link.name}
                        </Link>
                    ))}

                    <div className="border-t border-gray-200 pt-3">
                        {user ? (
                            <>
                                <Link
                                    href={route('dashboard')}
                                    className="block rounded-md px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900"
                                >
                                    Dashboard
                                </Link>
                                <Link
                                    href={route('logout')}
                                    method="post"
                                    as="button"
                                    className="block w-full rounded-md px-3 py-2 text-left text-sm font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900"
                                >
                                    Log Out
                                </Link>
                            </>
                        ) : (
                            <>
                                <Link
                                    href={route('login')}
                                    className="block rounded-md px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900"
                                >
                                    Log in
                                </Link>
                                <Link
                                    href={route('register')}
                                    className="block rounded-md px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900"
                                >
                                    Sign Up
                                </Link>
                            </>
                        )}
                    </div>
                </div>
            )}
        </nav>
    );
}
