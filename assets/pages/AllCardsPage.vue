<script setup>
import { onMounted, ref, watch } from 'vue';
import { fetchAllCards, fetchSetCodes } from '../services/cardService';

const cards = ref([]);
const setCodes = ref([]);
const selectedSet = ref('');
const loadingCards = ref(true);
const currentPage = ref(1);
const limit = 100;

async function loadCards() {
    loadingCards.value = true;
    try {
        cards.value = await fetchAllCards({
            setCode: selectedSet.value || null,
            page: currentPage.value,
            limit: limit,
        });
    } catch (error) {
        console.error("Failed to load cards:", error);
        cards.value = [];
    } finally {
        loadingCards.value = false;
    }
}

async function loadSetCodes() {
    try {
        setCodes.value = await fetchSetCodes();
    } catch (error) {
        console.error("Failed to load set codes:", error);
    }
}

function nextPage() {
    currentPage.value++;
    loadCards();
}

function previousPage() {
    if (currentPage.value > 1) {
        currentPage.value--;
        loadCards();
    }
}

watch(selectedSet, () => {
    currentPage.value = 1; // Revenir à la première page quand le filtre change
    loadCards();
});

onMounted(() => {
    loadCards();
    loadSetCodes();
});

</script>

<template>
    <div>
        <h1>Toutes les cartes</h1>

        <div class="filters">
            <label for="set-selector">Filtrer par set : </label>
            <select id="set-selector" v-model="selectedSet">
                <option value="">-- Tous les sets --</option>
                <option v-for="code in setCodes" :key="code" :value="code">{{ code }}</option>
            </select>
        </div>
    </div>

    <div class="pagination">
        <button @click="previousPage" :disabled="currentPage <= 1 || loadingCards">Précédent</button>
        <span>Page {{ currentPage }}</span>
        <button @click="nextPage" :disabled="cards.length < limit || loadingCards">Suivant</button>
    </div>

    <div class="card-list">
        <div v-if="loadingCards">Loading...</div>
        <div v-else>
            <div v-if="cards.length === 0" class="no-results">Aucune carte trouvée.</div>
            <div v-else class="card-result" v-for="card in cards" :key="card.uuid">
                <router-link :to="{ name: 'get-card', params: { uuid: card.uuid } }">
                    {{ card.name }} <span>({{ card.uuid }})</span>
                </router-link>
            </div>
        </div>
    </div>

    <div class="pagination" v-if="!loadingCards && cards.length > 0">
        <button @click="previousPage" :disabled="currentPage <= 1">Précédent</button>
        <span>Page {{ currentPage }}</span>
        <button @click="nextPage" :disabled="cards.length < limit">Suivant</button>
    </div>
</template>
