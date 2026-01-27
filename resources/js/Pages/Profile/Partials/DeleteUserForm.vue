<template>
    <section :class="['space-y-6', className]">
        <header>
            <h2 class="text-lg font-medium text-gray-900">Delete Account</h2>
            <p class="mt-1 text-sm text-gray-600">
                Once your account is deleted, all of its resources and data will be permanently deleted. Before
                deleting your account, please download any data or information that you wish to retain.
            </p>
        </header>

        <DangerButton @click="confirmUserDeletion">Delete Account</DangerButton>

        <Modal :show="confirmingUserDeletion" @close="closeModal">
            <form @submit.prevent="deleteUser" class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    Are you sure you want to delete your account?
                </h2>

                <p class="mt-1 text-sm text-gray-600">
                    Once your account is deleted, all of its resources and data will be permanently deleted. Please
                    enter your password to confirm you would like to permanently delete your account.
                </p>

                <div class="mt-6">
                    <InputLabel for="password" value="Password" class="sr-only" />
                    <TextInput
                        id="password"
                        type="password"
                        name="password"
                        ref="passwordInput"
                        v-model="form.password"
                        class="mt-1 block w-3/4"
                        :is-focused="true"
                        placeholder="Password"
                    />
                    <InputError :message="errors.password" class="mt-2" />
                </div>

                <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="closeModal">Cancel</SecondaryButton>
                    <DangerButton class="ms-3" :disabled="form.processing">
                        Delete Account
                    </DangerButton>
                </div>
            </form>
        </Modal>
    </section>
</template>

<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import DangerButton from '@/Components/DangerButton.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    className: {
        type: String,
        default: '',
    },
});

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({
    password: '',
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;
};

const deleteUser = () => {
    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value?.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;
    form.reset();
};
</script>

