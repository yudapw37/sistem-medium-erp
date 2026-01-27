<template>
    <GuestLayout>
        <Head title="Forgot Password" />

        <div class="mb-4 text-sm text-gray-600">
            Forgot your password? No problem. Just let us know your email address and we will email you a password
            reset link that will allow you to choose a new one.
        </div>

        <div v-if="status" class="mb-4 font-medium text-sm text-green-600">
            {{ status }}
        </div>

        <form @submit.prevent="submit">
            <TextInput
                id="email"
                type="email"
                name="email"
                v-model="form.email"
                class="mt-1 block w-full"
                :is-focused="true"
            />

            <InputError :message="errors.email" class="mt-2" />

            <div class="flex items-center justify-end mt-4">
                <PrimaryButton class="ms-4" :disabled="form.processing">
                    Email Password Reset Link
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>

<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

defineProps({
    status: String,
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

