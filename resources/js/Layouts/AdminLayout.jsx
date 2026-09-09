import { Link } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import {
  Dialog,
  DialogPanel,
  Transition,
  TransitionChild,
} from '@headlessui/react';
import {
  CalendarDays,
  LayoutDashboard,
  LayoutTemplate,
  Mail,
  Menu,
  MessageSquareText,
  Music,
  Users,
  X,
} from 'lucide-react';

import Navbar from '@/Components/Navbar';
import { Button } from '@/components/ui/button';

export default function AdminLayout({ children }) {
  const [open, setOpen] = useState(false);

  useEffect(() => {
    if (open) {
      const previous = document.body.style.overflow;
      document.body.style.overflow = 'hidden';
      return () => {
        document.body.style.overflow = previous;
      };
    }
  }, [open]);

  const navItems = [
    {
      name: 'Dashboard',
      href: route('admin.dashboard'),
      active: route().current('admin.dashboard'),
      icon: LayoutDashboard,
    },
    {
      name: 'Users',
      href: route('admin.users.index'),
      active: route().current('admin.users*'),
      icon: Users,
    },
    {
      name: 'Templates',
      href: route('admin.templates.index'),
      active: route().current('admin.templates*'),
      icon: LayoutTemplate,
    },
    {
      name: 'Melodies',
      href: route('admin.melodies.index'),
      active: route().current('admin.melodies*'),
      icon: Music,
    },
    {
      name: 'Events',
      href: route('admin.events.index'),
      active: route().current('admin.events*'),
      icon: CalendarDays,
    },
    {
      name: 'Invitations',
      href: route('admin.invitations.index'),
      active: route().current('admin.invitations*'),
      icon: Mail,
    },
    {
      name: 'Responses',
      href: route('admin.responses.index'),
      active: route().current('admin.responses*'),
      icon: MessageSquareText,
    },
  ];

  const SidebarContent = () => (
    <>
      <div className="px-4 py-5">
        <p className="text-xs font-semibold uppercase tracking-wider text-muted-foreground">
          Admin Panel
        </p>
      </div>

      <nav className="flex-1 space-y-1 px-3">
        {navItems.map((item) => (
          <Link
            key={item.name}
            href={item.href}
            onClick={() => setOpen(false)}
            className={`flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium transition ${
              item.active
                ? 'bg-primary/10 text-primary'
                : 'text-muted-foreground hover:bg-muted hover:text-foreground'
            }`}
          >
            <item.icon className="size-4 shrink-0" />
            {item.name}
          </Link>
        ))}
      </nav>

      <div className="space-y-2 border-t border-border p-4">
        <Link href={route('home')}>
          <Button variant="ghost" size="sm" className="w-full justify-start">
            Back to site
          </Button>
        </Link>
        <Link href={route('logout')} method="post">
          <Button variant="outline" size="sm" className="w-full justify-start">
            Log Out
          </Button>
        </Link>
      </div>
    </>
  );

  return (
    <div className="flex min-h-screen flex-col bg-background">
      <Navbar />

      <div className="md:hidden">
        <div className="flex h-14 items-center justify-between border-b border-border bg-card px-4">
          <button
            onClick={() => setOpen(true)}
            className="inline-flex items-center gap-2 rounded-md p-2 text-sm font-medium text-muted-foreground transition hover:bg-muted hover:text-foreground"
            aria-label="Toggle admin navigation"
          >
            <Menu className="size-5" />
            Admin Panel
          </button>
        </div>
      </div>

      <div className="flex flex-1">
        <aside className="hidden w-64 shrink-0 flex-col border-r border-border bg-card md:flex">
          <SidebarContent />
        </aside>

        <main className="min-w-0 flex-1">
          <div className="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            {children}
          </div>
        </main>
      </div>

      <Transition show={open} leave="duration-200">
        <Dialog
          as="div"
          className="fixed inset-0 z-50"
          onClose={() => setOpen(false)}
        >
          <TransitionChild
            enter="ease-out duration-300"
            enterFrom="opacity-0"
            enterTo="opacity-100"
            leave="ease-in duration-200"
            leaveFrom="opacity-100"
            leaveTo="opacity-0"
          >
            <div className="absolute inset-0 bg-foreground/60" />
          </TransitionChild>

          <TransitionChild
            enter="ease-out duration-300"
            enterFrom="-translate-x-full"
            enterTo="translate-x-0"
            leave="ease-in duration-200"
            leaveFrom="translate-x-0"
            leaveTo="-translate-x-full"
          >
            <DialogPanel className="flex h-full w-64 transform flex-col overflow-y-auto border-r border-border bg-card transition">
              <button
                onClick={() => setOpen(false)}
                className="ml-auto mr-3 mt-3 rounded-md p-1.5 text-muted-foreground transition hover:bg-muted hover:text-foreground"
                aria-label="Close admin navigation"
              >
                <X className="size-5" />
              </button>
              <SidebarContent />
            </DialogPanel>
          </TransitionChild>
        </Dialog>
      </Transition>
    </div>
  );
}