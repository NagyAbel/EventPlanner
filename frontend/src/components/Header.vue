<script setup lang="ts">
import { useI18n } from 'vue-i18n'
import { useRouter } from 'vue-router'
import profile from '@/assets/profile.svg'
import { useAuthStore } from '@/stores/auth'

const { t } = useI18n()
const router = useRouter()
const auth = useAuthStore()

function goToProfile() {
   router.push('/profile')
}
function goToHome(){
   router.push('/')
}
</script>

<template>
    <div class="header">
            <div class="left_holder">
               <button class="home_button" type="button" :aria-label="t('header.goToHomeAria')" @click="goToHome">
                  {{ t("header.title") }}
               </button>

            </div>
            <div class = "right_holder">
                        <button class="profile_button" type="button" :aria-label="t('header.openProfileAria')" @click="goToProfile">
                           <img class="profile_icon" :src="profile" :alt="t('header.userProfileAlt')">
                            <p class="profile_name">{{ auth.user?.name ?? t('header.guest') }}</p>
                        </button>
            </div>
    </div>
</template>

<style scoped>
 .header{
    width: 100%;
    height: 100px;
    margin:0px;
   position: sticky;
   top: 0;
   z-index: 1000;

    background-color: var(--color-secondary);
    display: flex;
    justify-content: center;
    align-items: center;
 }
 .left_holder{
    box-sizing: border-box;
    display: flex;
    padding-left: 10px;
    align-items: center;
    height: 100%;
    width: 50%;
 }
 .home_button{
    border: 0;
    padding: 0;
    background: transparent;
    color: inherit;
    font: inherit;
    font-size: 2em;
    font-weight: bold;
    cursor: pointer;
 }
 .home_button:focus-visible{
    outline: 2px solid #ffffff;
    outline-offset: 2px;
 }
 .right_holder{
    width: 50%;
    height: 100%;
    display: flex;
    justify-content: flex-end;
    align-items: center;
    padding-right: 16px;
    box-sizing: border-box;
 }
 .profile_name{
    width: 60%;
    font-size: large;
    text-align: center;
    font-weight: bolder;
 }

 .profile_button {
    width: 200px;
    height: 50px;
    border: 0;
    border-radius: 30px;
    background: rgba(255, 255, 255, 0.22);
    color: #ffffff;
    display: inline-flex;
    align-items: center;
    justify-content: left;
    cursor: pointer;
 }
 .profile_icon{
    filter: brightness(0) invert(1);
    width: 50px;
    height: 60%;
 }
 .profile_button svg {
    width: 24px;
    height: 24px;
    fill: currentColor;
 }

 .profile_button:hover {
    background: rgba(255, 255, 255, 0.3);
 }

 .profile_button:focus-visible {
    outline: 2px solid #ffffff;
    outline-offset: 2px;
 }
</style>