import PrimaryButton from '@/Components/PrimaryButton';
import Navbar from '@/Components/Navbar';
import { Head, Link, useForm } from '@inertiajs/react';

export default function VerifyEmail({ status }) {
  const { post, processing } = useForm({});

  const submit = (e) => {
    e.preventDefault();

    post(route('verification.send'));
  };

  return (
    <div className="flex min-h-screen flex-col bg-background">
      <Head title="Email Verification" />
      <Navbar />

      <main className="flex flex-1 items-center justify-center px-4 py-12 sm:px-6 lg:px-8">
        <div className="w-full max-w-md rounded-xl bg-card p-8 shadow-sm">
          <div className="mb-4 text-sm text-muted-foreground">
            Thanks for signing up! Before getting started, could you verify your
            email address by clicking on the link we just emailed to you? If you
            didn't receive the email, we will gladly send you another.
          </div>

          {status === 'verification-link-sent' && (
            <div className="mb-4 text-sm font-medium text-green-600">
              A new verification link has been sent to the email address you
              provided during registration.
            </div>
          )}

          <form onSubmit={submit}>
            <div className="mt-4 flex items-center justify-between">
              <PrimaryButton disabled={processing}>
                Resend Verification Email
              </PrimaryButton>

              <Link
                href={route('logout')}
                method="post"
                as="button"
                className="rounded-md text-sm text-muted-foreground underline hover:text-foreground focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
              >
                Log Out
              </Link>
            </div>
          </form>
        </div>
      </main>
    </div>
  );
}