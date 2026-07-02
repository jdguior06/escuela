<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import SelectInput from '@/Components/SelectInput.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    form: {
        type: Object,
        required: true,
    },
    roles: {
        type: Array,
        required: true,
    },
    isEdit: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
        <div>
            <InputLabel for="nombre" value="Nombre" />
            <TextInput id="nombre" type="text" class="mt-1 block w-full" v-model="form.nombre" required autofocus />
            <InputError class="mt-2" :message="form.errors.nombre" />
        </div>

        <div>
            <InputLabel for="apellido" value="Apellido" />
            <TextInput id="apellido" type="text" class="mt-1 block w-full" v-model="form.apellido" required />
            <InputError class="mt-2" :message="form.errors.apellido" />
        </div>

        <div>
            <InputLabel for="nro_documento" value="Nro. de documento" />
            <TextInput id="nro_documento" type="text" class="mt-1 block w-full" v-model="form.nro_documento" required />
            <InputError class="mt-2" :message="form.errors.nro_documento" />
        </div>

        <div>
            <InputLabel for="correo" value="Correo electrónico" />
            <TextInput id="correo" type="email" class="mt-1 block w-full" v-model="form.correo" required />
            <InputError class="mt-2" :message="form.errors.correo" />
        </div>

        <div>
            <InputLabel for="telefono" value="Teléfono" />
            <TextInput id="telefono" type="text" class="mt-1 block w-full" v-model="form.telefono" />
            <InputError class="mt-2" :message="form.errors.telefono" />
        </div>

        <div>
            <InputLabel for="direccion" value="Dirección" />
            <TextInput id="direccion" type="text" class="mt-1 block w-full" v-model="form.direccion" />
            <InputError class="mt-2" :message="form.errors.direccion" />
        </div>

        <div>
            <InputLabel for="fecha_nacimiento" value="Fecha de nacimiento" />
            <TextInput id="fecha_nacimiento" type="date" class="mt-1 block w-full" v-model="form.fecha_nacimiento" />
            <InputError class="mt-2" :message="form.errors.fecha_nacimiento" />
        </div>

        <div>
            <InputLabel for="genero" value="Género" />
            <SelectInput id="genero" class="mt-1 block w-full" v-model="form.genero">
                <option value="">Sin especificar</option>
                <option value="M">Masculino</option>
                <option value="F">Femenino</option>
            </SelectInput>
            <InputError class="mt-2" :message="form.errors.genero" />
        </div>

        <div>
            <InputLabel for="estado_usuario" value="Estado" />
            <SelectInput id="estado_usuario" class="mt-1 block w-full" v-model="form.estado_usuario">
                <option value="activo">Activo</option>
                <option value="inactivo">Inactivo</option>
            </SelectInput>
            <InputError class="mt-2" :message="form.errors.estado_usuario" />
        </div>

        <div>
            <InputLabel for="rol_id" value="Rol" />
            <SelectInput id="rol_id" class="mt-1 block w-full" v-model="form.rol_id">
                <option value="" disabled>Selecciona un rol</option>
                <option v-for="rol in roles" :key="rol.id" :value="rol.id">{{ rol.nombre }}</option>
            </SelectInput>
            <InputError class="mt-2" :message="form.errors.rol_id" />
        </div>

        <div>
            <InputLabel for="password" :value="isEdit ? 'Nueva contraseña (opcional)' : 'Contraseña'" />
            <TextInput
                id="password"
                type="password"
                class="mt-1 block w-full"
                v-model="form.password"
                :required="!isEdit"
                autocomplete="new-password"
            />
            <InputError class="mt-2" :message="form.errors.password" />
        </div>

        <div>
            <InputLabel for="password_confirmation" value="Confirmar contraseña" />
            <TextInput
                id="password_confirmation"
                type="password"
                class="mt-1 block w-full"
                v-model="form.password_confirmation"
                :required="!isEdit"
                autocomplete="new-password"
            />
            <InputError class="mt-2" :message="form.errors.password_confirmation" />
        </div>
    </div>
</template>
