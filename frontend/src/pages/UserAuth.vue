<script setup lang="ts">
import { useI18n } from 'vue-i18n'

const { t } = useI18n()
</script>
<template>
  <div>
    <h1>Sign Up</h1>

    <form>
      <div>
        <label for="name">{{t('signup.title')}}</label>
        <input id="name" type="text" />
      </div>

      <div>
        <label for="email">{{t('signup.email')}}</label>
        <input id="email" type="email" />
      </div>

      <div>
        <label for="password">{{t('signup.password')}}</label>
        <input id="password" type="password" />
      </div>

      <button type="submit">{{ t('signup.button') }}</button>
    </form>
  </div>
</template>
<style scoped>
div {
  max-width: 400px;
  margin: 50px auto;
}

h1 {
  margin-bottom: 30px;
}

form {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

form div {
  margin: 0;
}

label {
  display: block;
  margin-bottom: 6px;
}

input {
  width: 100%;
  padding: 10px;
  border: 1px solid #ccc;
  border-radius: 6px;
  box-sizing: border-box;
}

button {
  padding: 10px;
  border: none;
  border-radius: 6px;
  background: #4f46e5;
  color: white;
  cursor: pointer;
}

button:hover {
  background: #4338ca;
}
</style>