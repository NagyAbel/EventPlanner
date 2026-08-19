<template>
  <div class="user-settings">
    <h1>Profile</h1>

    <div class="profile-field">
      <label>First Name</label>
      <input v-model="user.first_name" type="text" />
    </div>

    <div class="profile-field">
      <label>Last Name</label>
      <input v-model="user.last_name" type="text" />
    </div>

    <div class="profile-field">
      <label>Email</label>
      <p>{{ user.email }}</p>
    </div>

    <button type="button" @click="logout">Logout</button>
  </div>
</template>

<script setup>
import { reactive } from 'vue'

const user = reactive({
  first_name: 'John',
  last_name: 'Doe',
  email: 'john.doe@example.com',
})

const logout = () => {
  localStorage.removeItem('token')
  window.location.href = '/login'
}
</script>

<style scoped>
.user-settings {
  max-width: 520px;
  margin: 0 auto;
  padding: 16px;
  text-align: center;
}

.profile-field {
  margin-bottom: 12px;
}

.profile-field label {
  display: block;
  font-weight: 600;
  margin-bottom: 4px;
}

input {
  width: 100%;
  box-sizing: border-box;
  padding: 8px;
}

button {
  margin-top: 8px;
  padding: 8px 14px;
  cursor: pointer;
}
</style>