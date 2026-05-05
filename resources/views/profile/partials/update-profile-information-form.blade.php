<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Perfil de información') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __("Actualiza la información de perfil y la dirección de correo electrónico de tu cuenta.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    @if(in_array(auth()->user()->role, ['supervisor', 'bodeguero', 'cajero']))
        <div class="mt-6 space-y-6">
            <div>
                <x-input-label :value="__('Nombre completo')" />
                <div class="mt-1 rounded-md border border-gray-300 bg-gray-50 p-3 text-gray-700 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-200">
                    {{ trim(sprintf('%s %s %s %s', $user->first_name, $user->middle_name, $user->last_name, $user->second_last_name)) }}
                </div>
            </div>

            <div>
                <x-input-label :value="__('Email')" />
                <div class="mt-1 rounded-md border border-gray-300 bg-gray-50 p-3 text-gray-700 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-200">
                    {{ $user->email }}
                </div>
            </div>

            <div>
                <x-input-label :value="__('Cargo')" />
                <div class="mt-1 rounded-md border border-gray-300 bg-gray-50 p-3 text-gray-700 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-200">
                    {{ ucfirst($user->role) }}
                </div>
            </div>

            <div>
                <x-input-label :value="__('Sucursal')" />
                <div class="mt-1 rounded-md border border-gray-300 bg-gray-50 p-3 text-gray-700 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-200">
                    {{ $user->branch->name ?? __('No asignada') }}
                </div>
            </div>

            <div class="rounded-lg bg-yellow-50 border border-yellow-200 p-4 text-sm text-yellow-800">
                {{ __('Esta información es de solo consulta y no puede actualizarse desde este usuario.') }}
            </div>

        </div>
    @else
        <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
            @csrf
            @method('patch')

            <div>
                <x-input-label for="first_name" :value="__('Primer Nombre')" />
                <x-text-input id="first_name" name="first_name" type="text" class="mt-1 block w-full" :value="old('first_name', $user->first_name)" required autofocus autocomplete="given-name" />
                <x-input-error class="mt-2" :messages="$errors->get('first_name')" />
            </div>

            <div>
                <x-input-label for="middle_name" :value="__('Segundo Nombre')" />
                <x-text-input id="middle_name" name="middle_name" type="text" class="mt-1 block w-full" :value="old('middle_name', $user->middle_name)" autocomplete="additional-name" />
                <x-input-error class="mt-2" :messages="$errors->get('middle_name')" />
            </div>

            <div>
                <x-input-label for="last_name" :value="__('Primer Apellido')" />
                <x-text-input id="last_name" name="last_name" type="text" class="mt-1 block w-full" :value="old('last_name', $user->last_name)" required autocomplete="family-name" />
                <x-input-error class="mt-2" :messages="$errors->get('last_name')" />
            </div>

            <div>
                <x-input-label for="second_last_name" :value="__('Segundo Apellido')" />
                <x-text-input id="second_last_name" name="second_last_name" type="text" class="mt-1 block w-full" :value="old('second_last_name', $user->second_last_name)" autocomplete="family-name" />
                <x-input-error class="mt-2" :messages="$errors->get('second_last_name')" />
            </div>

            <div>
                <x-input-label for="email" :value="__('Email')" />
                <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
                <x-input-error class="mt-2" :messages="$errors->get('email')" />
            </div>

            <div>
                <x-input-label :value="__('Cargo')" />
                <div class="mt-1 rounded-md border border-gray-300 bg-gray-50 p-3 text-gray-700 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-200">
                    {{ ucfirst($user->role) }}
                </div>
            </div>

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800 dark:text-gray-200">
                            {{ __('Your email address is unverified.') }}

                            <button form="send-verification" class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                                {{ __('Click here to re-send the verification email.') }}
                            </button>
                        </p>

                        @if (session('status') === 'verification-link-sent')
                            <p class="mt-2 font-medium text-sm text-green-600 dark:text-green-400">
                                {{ __('A new verification link has been sent to your email address.') }}
                            </p>
                        @endif
                    </div>
                @endif

            <div class="flex items-center gap-4">
                <x-primary-button>{{ __('Guardar Cambios') }}</x-primary-button>

                @if (session('status') === 'profile-updated')
                    <p
                        x-data="{ show: true }"
                        x-show="show"
                        x-transition
                        x-init="setTimeout(() => show = false, 2000)"
                        class="text-sm text-gray-600 dark:text-gray-400"
                    >{{ __('Cambios Guardados.') }}</p>
                @endif
            </div>
        </form>
    @endif
</section>
