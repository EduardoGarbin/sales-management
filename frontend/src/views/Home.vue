<template>
    <div class="container">
        <h1>Sales Management System</h1>
        <div class="cards">
            <router-link to="/sellers" class="card">
                <h2>Vendedores</h2>
                <p>Gerenciar vendedores</p>
            </router-link>
            <router-link to="/sales" class="card">
                <h2>Vendas</h2>
                <p>Gerenciar vendas</p>
            </router-link>
        </div>
    </div>
</template>

<script setup lang="ts">
</script>
