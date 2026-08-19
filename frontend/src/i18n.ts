import { createI18n } from 'vue-i18n'

import en from './locales/en.json'
import hu from './locales/hu.json'

const i18n = createI18n({
  legacy: false,
  locale: 'en',
  fallbackLocale: 'en',

  messages: {
    en,
    hu
  }
})

export default i18n