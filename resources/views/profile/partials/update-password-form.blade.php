<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Cập nhật mật khẩu') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('Đảm bảo tài khoản của bạn sử dụng mật khẩu dài và ngẫu nhiên để đảm bảo an toàn.') }}
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('put')

    <!-- Trường mật khẩu hiện tại -->
    <div>
        <x-input-label for="update_password_current_password" :value="__('Mật khẩu hiện tại')" />
        <x-text-input id="update_password_current_password" name="current_password" type="password" class="mt-1 block w-full" autocomplete="current-password" />
        
        @if ($errors->updatePassword->has('current_password'))
            <p class="text-sm text-red-600 mt-2">
                @php
                    $msg = $errors->updatePassword->first('current_password');
                @endphp

                @if (str_contains($msg, 'incorrect'))
                    Mật khẩu hiện tại không đúng.
                @else
                    {{ $msg }}
                @endif
            </p>
        @endif
    </div>

    <!-- Trường mật khẩu mới -->
    <div>
        <x-input-label for="update_password_password" :value="__('Mật khẩu mới')" />
        <x-text-input id="update_password_password" name="password" type="password" class="mt-1 block w-full" autocomplete="new-password" />

        @if ($errors->updatePassword->has('password'))
            <p class="text-sm text-red-600 mt-2">
                @php
                    $msg = $errors->updatePassword->first('password');
                @endphp

                @switch(true)
                    @case(str_contains($msg, 'at least'))
                        Mật khẩu phải có ít nhất 8 ký tự.
                        @break

                    @case(str_contains($msg, 'confirmed'))
                        Mật khẩu xác nhận không khớp.
                        @break

                    @default
                        {{ $msg }}
                @endswitch
            </p>
        @endif
    </div>

    <!-- Trường nhập lại mật khẩu -->
    <div>
        <x-input-label for="update_password_password_confirmation" :value="__('Nhập lại mật khẩu mới')" />
        <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" autocomplete="new-password" />

        @if ($errors->updatePassword->has('password_confirmation'))
            <p class="text-sm text-red-600 mt-2">
                @php
                    $msg = $errors->updatePassword->first('password_confirmation');
                @endphp

                @if (str_contains($msg, 'match'))
                    Mật khẩu xác nhận không đúng.
                @else
                    {{ $msg }}
                @endif
            </p>
        @endif
    </div>


        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Cập nhật') }}</x-primary-button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600 dark:text-gray-400"
                >{{ __('Cập nhật thành công!.') }}</p>
            @endif
        </div>
    </form>
</section>
