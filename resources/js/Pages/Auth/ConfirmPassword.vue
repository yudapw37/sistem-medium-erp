<template>
    <GuestLayout>
        <Head title="Confirm Password" />

        <div class="mb-4 text-sm text-gray-600">
            This is a secure area of the application. Please confirm your password before continuing.
        </div>

        <form @submit.prevent="submit">
            <div class="mt-4">
                <InputLabel for="password" value="Password" />

                <TextInput
                    id="password"
                    type="password"
                    name="password"
                    v-model="form.password"
                    class="mt-1 block w-full"
                    :is-focused="true"
                />

                <InputError :message="errors.password" class="mt-2" />
            </div>

            <div class="flex items-center justify-end mt-4">
                <PrimaryButton class="ms-4" :disabled="form.processing">
                    Confirm
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

const form = useForm({
    password: '',
});

const submit = () => {
    form.post(route('password.confirm'));
};

onUnmounted(() => {
    form.reset('password');
});
</script>

