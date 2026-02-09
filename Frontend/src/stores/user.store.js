import { ref } from 'vue';
import { defineStore } from 'pinia';
import api from '../services/api.js';
import {useUsersStore} from '@/stores/users.store.js';

export const useUserStore = defineStore('user', () => {
    const usersStore = useUsersStore();
    const currentUser = ref(null);




  return { currentUser };

});

  
  