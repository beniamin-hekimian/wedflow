import { useRef } from 'react';
import Checkbox from '@/Components/Checkbox';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import Navbar from '@/Components/Navbar';
import { Head, Link, useForm } from '@inertiajs/react';

export default function Login({ status }) {
  const passwordRef = useRef(null);

  const { data, setData, post, processing, errors, reset } = useForm({
    email: '',
    password: '',
    remember: false,
  });

  const submit = (e) => {
    e.preventDefault();
    post(route('login'), {
      onFinish: () => reset('password'),
    });
  };

  return (
    <div className="flex min-h-screen flex-col bg-gray-50">
      <Head title="Log in" />
      <Navbar />

      <main className="flex flex-1 items-center justify-center px-4 py-12 sm:px-6 lg:px-8">
        <div className="w-full max-w-md rounded-xl bg-white p-8 shadow-sm">
          <div className="mb-6 text-center">
            <h1 className="text-2xl font-bold text-gray-900">Welcome back</h1>
            <p className="mt-1 text-sm text-gray-500">
              Log in to manage your wedding invitations.
            </p>
          </div>

          {status && (
            <div className="mb-4 text-sm font-medium text-green-600">
              {status}
            </div>
          )}

          <form onSubmit={submit}>
            <div>
              <InputLabel htmlFor="email" value="Email" />

              <TextInput
                id="email"
                type="email"
                name="email"
                value={data.email}
                className="mt-1 block w-full"
                autoComplete="username"
                isFocused={true}
                onChange={(e) => setData('email', e.target.value)}
                onKeyDown={(e) => {
                  if (e.key === 'Enter') {
                    e.preventDefault();
                    passwordRef.current?.focus();
                  }
                }}
              />

              <InputError message={errors.email} className="mt-2" />
            </div>

            <div className="mt-4">
              <InputLabel htmlFor="password" value="Password" />

              <TextInput
                id="password"
                type="password"
                name="password"
                value={data.password}
                className="mt-1 block w-full"
                autoComplete="current-password"
                ref={passwordRef}
                onChange={(e) => setData('password', e.target.value)}
              />

              <InputError message={errors.password} className="mt-2" />
            </div>

            <div className="mt-4 block">
              <label className="flex items-center">
                <Checkbox
                  name="remember"
                  checked={data.remember}
                  onChange={(e) => setData('remember', e.target.checked)}
                />
                <span className="ms-2 text-sm text-gray-600">Remember me</span>
              </label>
            </div>

            <div className="mt-4 flex items-center justify-end">
              <Link
                href={route('register')}
                className="rounded-md text-sm text-gray-600 underline transition hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
              >
                Don't have an account?
              </Link>

              <PrimaryButton className="ms-4" disabled={processing}>
                Log in
              </PrimaryButton>
            </div>
          </form>
        </div>
      </main>
    </div>
  );
}