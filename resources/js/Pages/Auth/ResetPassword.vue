<template>
    <GuestLayout>
        <Head title="Reset Password" />

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="email" value="Email" />

                <TextInput
                    id="email"
                    type="email"
                    name="email"
                    v-model="form.email"
                    class="mt-1 block w-full"
                    autocomplete="username"
                />

                <InputError :message="errors.email" class="mt-2" />
            </div>

            <div class="mt-4">
                <InputLabel for="password" value="Password" />

                <TextInput
                    id="password"
                    type="password"
                    name="password"
                    v-model="form.password"
                    class="mt-1 block w-full"
                    autocomplete="new-password"
                    :is-focused="true"
                />

                <InputError :message="errors.password" class="mt-2" />
            </div>

            <div class="mt-4">
                <InputLabel for="password_confirmation" value="Confirm Password" />

                <TextInput
                    type="password"
                    id="password_confirmation"
                    name="password_confirmation"
                    v-model="form.password_confirmation"
                    class="mt-1 block w-full"
                    autocomplete="new-password"
                />

                <InputError :message="errors.password_confirmation" class="mt-2" />
            </div>

            <div class="flex items-center justify-end mt-4">
                <PrimaryButton class="ms-4" :disabled="form.processing">
                    Reset Password
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>

<script setup>
import { onUnmounted } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    token: String,
    email: String,
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.store'));
};

onUnmounted(() => {
    form.reset('password', 'password_confirmation');
});
</script>

