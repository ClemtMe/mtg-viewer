<script setup>
import { ref } from 'vue';
import { searchCard } from '../services/cardService';

const card = ref();
const searchQuery = ref('');
const loadingCard = ref(false);

async function search() {
  loadingCard.value = true;
  try {
    const result = await searchCard(searchQuery.value);
    card.value = result || null;
  } catch (error) {
    console.error("Erreur lors de la recherche:", error);
    card.value = null;
  } finally {
    loadingCard.value = false;
  }
}

async function handleSearch() {
  if (searchQuery.value.length < 3) {
    card.value = null;
    return;
  }

  if (window.searchTimeout) {
    clearTimeout(window.searchTimeout);
  }

  window.searchTimeout = setTimeout(() => {
    search();
  }, 300)
}
</script>

<template>
    <div>
        <h1>Rechercher une Carte</h1>
        <div class="search-bar">
            <input
                type="text"
                v-model="searchQuery"
                @input="handleSearch"
                placeholder="Entrez au moins 3 caractères..."
                class="search-input"
            />
        </div>

        <div class="search-results">
            <div v-if="loadingCard">Chargement...</div>
            <div v-else-if="card">
                <router-link :to="{ name: 'get-card', params: { uuid: card.uuid } }">
                    {{ card.name }} <span>({{ card.uuid }})</span>
                </router-link>
            </div>
            <div v-else-if="searchQuery.length >= 3">
                Aucune carte trouvée.
            </div>
        </div>


    </div>
</template>
