<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Thông tin hồ sơ') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __("Cập nhật thông tin hồ sơ tài khoản.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')
    
        <div>
            <x-input-label for="username" :value="__('Tên người dùng')" />
            <x-text-input id="username" name="username" type="text" class="mt-1 block w-full"
                          :value="old('username', $user->username)" required />
            <x-input-error class="mt-2" :messages="$errors->get('username')" />
        </div>
    
        <div>
            <x-input-label for="full_name" :value="__('Họ và tên')" />
            <x-text-input id="full_name" name="full_name" type="text" class="mt-1 block w-full"
                          :value="old('full_name', $user->full_name)" required />
            <x-input-error class="mt-2" :messages="$errors->get('full_name')" />
        </div>
    
        <div>
            <x-input-label for="birth_date" :value="__('Ngày Sinh')" />
            <x-text-input id="birth_date" name="birth_date" type="date" class="mt-1 block w-full"
            :value="old('birth_date', $user->birth_date ? $user->birth_date->format('Y-m-d') : '')" />
            <x-input-error class="mt-2" :messages="$errors->get('birth_date')" />
        </div>
    
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full"
                          :value="old('email', $user->email)" required />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />
        </div>
    
        <div>
            <x-input-label for="address" :value="__('Địa chỉ')" />
            <x-text-input id="address" name="address" type="text" class="mt-1 block w-full"
                          :value="old('address', $user->address)" />
            <x-input-error class="mt-2" :messages="$errors->get('address')" />
        </div>
    
        <div>
            <x-input-label for="phone_number" :value="__('Số điện thoại')" />
            <x-text-input id="phone_number" name="phone_number" type="text" class="mt-1 block w-full"
                pattern="^\d{10}$" maxlength="10"
                title="Số điện thoại phải gồm đúng 10 chữ số"
                :value="old('phone_number', $user->phone_number)" required />
            <x-input-error class="mt-2" :messages="$errors->get('phone_number')" />
        </div>        
    
        <div>
            <x-input-label for="role" :value="__('Quyền')" />
            <x-text-input id="role" name="role" type="text" class="mt-1 block w-full bg-gray-200 text-gray-700 cursor-not-allowed"
                          :value="$user->role" readonly />
        </div>
        
        
        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Cập nhật') }}</x-primary-button>
    
            @if (session('status') === 'profile-updated')
                <p x-data="{ show: true }" x-show="show" x-transition
                   x-init="setTimeout(() => show = false, 2000)"
                   class="text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Cập nhật thành công.') }}
                </p>
            @endif
        </div>
    </form>
    
</section>
