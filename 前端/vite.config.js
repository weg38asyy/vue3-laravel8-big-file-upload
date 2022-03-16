import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import path from 'path'

// Reference: https://vitejs.dev/config/
export default defineConfig({
  base: process.env.NODE_ENV === 'production' ? './' : '/',
  // 前端開發時的Port 及 後端Port
  server:{
    host:'localhost',
    // Dev Port
    port: 8000,
    proxy:{
      // 正式環境網址，請參考Config/index.js
      "/api":{
        target: "http://localhost:3000"
      }
    },
  },
  plugins: [vue()],

  build:{
	  target: 'es2015',
	  outDir: 'dist/',
  },
  
  // alias
  resolve:{
    alias:{
      '@': path.resolve(__dirname, 'src')
    }
  }

})


