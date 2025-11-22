<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Teste Prefiro Delivery</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

    <style>
        .fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease; }
        .fade-enter-from, .fade-leave-to { opacity: 0; }
        .slide-enter-active, .slide-leave-active { transition: all 0.3s ease-out; max-height: 1000px; overflow: hidden; }
        .slide-enter-from, .slide-leave-to { max-height: 0; opacity: 0; }
        
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased h-screen overflow-hidden">

    <div id="app" class="flex h-full relative">
        
        <aside class="w-full md:w-80 bg-white border-r border-slate-200 flex flex-col shadow-xl z-20 transition-transform duration-300 absolute md:relative h-full"
               :class="showMobileMenu ? 'translate-x-0' : '-translate-x-full md:translate-x-0'">
            
            <div class="p-6 bg-indigo-600 text-white shadow-md">
                <div class="flex justify-between items-center">
                    <h1 class="text-xl font-bold flex items-center gap-2">
                        <i class="ph ph-tree-structure text-2xl"></i>
                        Teste Laravel - Lucas Santana
                    </h1>
                    <button @click="showMobileMenu = false" class="md:hidden text-white hover:bg-indigo-700 p-1 rounded"><i class="ph ph-x text-xl"></i></button>
                </div>
                <div class="mt-4 pt-4 border-t border-indigo-500/50 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full bg-indigo-800 flex items-center justify-center font-bold text-xs">
                            {{ substr(auth()->user()->name, 0, 2) }}
                        </div>
                        <div class="text-sm">
                            <p class="font-bold leading-none">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-indigo-200 leading-none mt-1">Online</p>
                        </div>
                    </div>
                    
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" title="Sair" class="text-indigo-200 hover:text-white hover:bg-indigo-700 p-1.5 rounded transition">
                            <i class="ph ph-sign-out text-xl"></i>
                        </button>
                    </form>
                </div>
            </div>

            <div class="p-6 flex-1 overflow-y-auto">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-bold text-slate-700 flex items-center gap-2">
                        <i :class="isEditing ? 'ph ph-pencil-simple text-orange-500' : 'ph ph-plus-circle text-indigo-500'"></i>
                        @{{ isEditing ? 'Editar' : 'Adicionar' }}
                    </h2>
                    <button v-if="isEditing" @click="resetForm" class="text-xs text-red-500 hover:underline">Cancelar</button>
                </div>

                <form @submit.prevent="submitForm" class="space-y-4">
                    <div class="group">
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Nome</label>
                        <input v-model="form.name" type="text" required
                            class="w-full bg-slate-50 border border-slate-200 text-slate-800 rounded-lg p-2.5 focus:ring-2 focus:ring-indigo-500 outline-none transition-all placeholder:text-slate-300"
                            placeholder="Ex: Eletrônicos">
                    </div>

                    <div class="group">
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Descrição</label>
                        <textarea v-model="form.description" rows="3"
                            class="w-full bg-slate-50 border border-slate-200 text-slate-800 rounded-lg p-2.5 focus:ring-2 focus:ring-indigo-500 outline-none transition-all placeholder:text-slate-300 resize-none"
                            placeholder="Detalhes..."></textarea>
                    </div>

                    <div class="group">
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Pertence a</label>
                        <div class="relative">
                            <select v-model="form.parent_id" 
                                class="w-full bg-slate-50 border border-slate-200 text-slate-800 rounded-lg p-2.5 pr-8 focus:ring-2 focus:ring-indigo-500 outline-none transition-all appearance-none cursor-pointer">
                                <option :value="null" class="font-bold text-indigo-600">Principal</option>
                                <option disabled>-------------------</option>
                                <option v-for="cat in flatCategories" :key="cat.id" :value="cat.id" :disabled="cat.id === form.id">
                                    @{{ cat.name }}
                                </option>
                            </select>
                            <i class="ph ph-caret-down absolute right-3 top-3 text-slate-400 pointer-events-none"></i>
                        </div>
                    </div>

                    <button type="submit" :disabled="loading"
                        class="w-full bg-indigo-600 hover:bg-indigo-700 disabled:bg-indigo-300 text-white font-bold py-3 rounded-lg shadow-lg shadow-indigo-200 transition-all active:scale-95 flex justify-center items-center gap-2 mt-4">
                        <i v-if="loading" class="ph ph-spinner animate-spin text-xl"></i>
                        <span v-else>@{{ isEditing ? 'Salvar' : 'Criar' }}</span>
                    </button>
                </form>

                <transition name="fade">
                    <div v-if="message.text" 
                        :class="`mt-6 p-4 rounded-lg text-sm flex items-start gap-3 border ${message.type === 'success' ? 'bg-emerald-50 text-emerald-700 border-emerald-100' : 'bg-red-50 text-red-700 border-red-100'}`">
                        <i :class="`text-lg mt-0.5 ${message.type === 'success' ? 'ph ph-check-circle-fill' : 'ph ph-warning-circle-fill'}`"></i>
                        <span class="font-medium">@{{ message.text }}</span>
                    </div>
                </transition>
            </div>
        </aside>

        <main class="flex-1 flex flex-col h-full overflow-hidden relative bg-slate-50/50">
            <div class="md:hidden bg-white p-4 border-b border-slate-200 flex justify-between items-center">
                <span class="font-bold text-slate-700">Categorias</span>
                <button @click="showMobileMenu = true" class="text-indigo-600 p-1"><i class="ph ph-list text-2xl"></i></button>
            </div>

            <div class="bg-white border-b border-slate-200 p-4 md:p-6 sticky top-0 z-10">
                <div class="relative max-w-2xl mx-auto">
                    <i class="ph ph-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-lg"></i>
                    <input v-model="searchQuery" type="text" 
                        class="w-full pl-11 pr-4 py-3 bg-slate-50 border-none rounded-full focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all shadow-sm placeholder:text-slate-400"
                        placeholder="Pesquisar na árvore...">
                </div>
            </div>

            <div class="flex-1 overflow-y-auto p-4 md:p-8 scroll-smooth">
                <div class="max-w-3xl mx-auto">
                    <div v-if="loading && categories.length === 0" class="flex flex-col items-center justify-center py-20 text-slate-400">
                        <i class="ph ph-spinner-gap animate-spin text-4xl mb-3 text-indigo-500"></i>
                        <p class="text-sm font-medium">Carregando...</p>
                    </div>

                    <div v-else-if="filteredCategories.length === 0" class="text-center py-16 bg-white rounded-2xl border border-dashed border-slate-300">
                        <div class="bg-slate-50 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="ph ph-tree-palm text-slate-300 text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-bold text-slate-800 mb-1">Nada por aqui</h3>
                        <p class="text-slate-500 text-sm">Nenhuma categoria encontrada.</p>
                    </div>

                    <ul v-else class="space-y-3 pb-20">
                        <tree-item 
                            v-for="category in filteredCategories" 
                            :key="category.id" 
                            :item="category" 
                            :level="0"
                        ></tree-item>
                    </ul>
                </div>
            </div>
        </main>
    </div>

    <script type="text/x-template" id="tree-item-template">
        <li>
            <div :class="`
                group relative bg-white border border-slate-200 rounded-xl transition-all duration-200 hover:shadow-md hover:border-indigo-200
                ${isOpen && hasChildren ? 'mb-3' : 'mb-2'}
            `">
                <div v-if="level > 0" class="absolute -left-4 top-1/2 w-4 h-px bg-slate-300"></div>

                <div class="p-3.5 flex items-center justify-between">
                    <div class="flex items-center gap-3 flex-1 cursor-pointer select-none" @click="toggle">
                        <button v-if="hasChildren" 
                            class="w-6 h-6 flex items-center justify-center rounded hover:bg-indigo-50 text-slate-400 hover:text-indigo-600 transition-colors">
                            <i :class="isOpen ? 'ph ph-caret-down-fill' : 'ph ph-caret-right-fill'"></i>
                        </button>
                        <div v-else class="w-6 opacity-20 text-center"><i class="ph ph-dot text-2xl"></i></div>

                        <div class="flex flex-col">
                            <span class="font-bold text-slate-700 text-sm md:text-base group-hover:text-indigo-700 transition-colors">
                                @{{ item.name }}
                            </span>
                            <span v-if="item.description" class="text-xs text-slate-400 font-medium truncate max-w-[150px] md:max-w-md">
                                @{{ item.description }}
                            </span>
                        </div>

                        <span v-if="hasChildren" class="ml-2 px-2 py-0.5 bg-indigo-50 text-indigo-600 text-[10px] font-bold uppercase rounded-full tracking-wider">
                            @{{ item.children.length }} Sub
                        </span>
                    </div>

                    <div class="flex items-center gap-1 opacity-100 md:opacity-0 md:group-hover:opacity-100 transition-all scale-95 md:group-hover:scale-100">
                        <button @click.stop="edit(item)" class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all" title="Editar">
                            <i class="ph ph-pencil-simple text-lg"></i>
                        </button>
                        <button @click.stop="remove(item.id)" class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all" title="Excluir">
                            <i class="ph ph-trash text-lg"></i>
                        </button>
                    </div>
                </div>
            </div>

            <transition name="slide">
                <ul v-if="hasChildren && isOpen" class="pl-6 md:pl-8 border-l-2 border-slate-100 ml-3 md:ml-4 space-y-2">
                    <tree-item 
                        v-for="child in item.children" 
                        :key="child.id" 
                        :item="child" 
                        :level="level + 1"
                    ></tree-item>
                </ul>
            </transition>
        </li>
    </script>

    <script>
        const { createApp, ref, computed, onMounted, inject } = Vue;

        const TreeItemComponent = {
            template: '#tree-item-template',
            props: ['item', 'level'],
            setup(props) {
                const isOpen = ref(true);
                const hasChildren = computed(() => props.item.children && props.item.children.length > 0);
                const toggle = () => { if (hasChildren.value) isOpen.value = !isOpen.value; };
                
                const edit = inject('editCategory');
                const remove = inject('deleteCategory');

                return { isOpen, hasChildren, toggle, edit, remove };
            }
        };

        const app = createApp({
            setup() {
                const configureAxios = () => {
                    const tokenMeta = document.querySelector('meta[name="csrf-token"]');
                    if (tokenMeta) {
                        axios.defaults.headers.common['X-CSRF-TOKEN'] = tokenMeta.getAttribute('content');
                    }
                    axios.defaults.headers.common['Accept'] = 'application/json';
                    axios.defaults.baseURL = '/api';
                };

                const categories = ref([]);
                const loading = ref(false);
                const searchQuery = ref('');
                const showMobileMenu = ref(false);
                const isEditing = ref(false);
                const message = ref({ text: '', type: '' });
                const form = ref({ id: null, name: '', description: '', parent_id: null });

                const { provide } = Vue;

                const fetchCategories = async () => {
                    loading.value = true;
                    try {
                        const response = await axios.get('/categorias');
                        categories.value = response.data;
                    } catch (error) {
                        console.error("Erro API:", error);
                        useMockData();
                    } finally {
                        loading.value = false;
                    }
                };

                const submitForm = async () => {
                    if (!form.value.name) return;
                    loading.value = true;
                    try {
                        if (isEditing.value) {
                            await axios.put(`/categorias/${form.value.id}`, form.value);
                            showMessage('Categoria atualizada!', 'success');
                        } else {
                            await axios.post('/categorias', form.value);
                            showMessage('Categoria criada!', 'success');
                        }
                        resetForm();
                        fetchCategories();
                    } catch (error) {
                        console.error(error);
                        showMessage('Erro ao salvar.', 'error');
                    } finally {
                        loading.value = false;
                    }
                };

                const deleteCategory = async (id) => {
                    if (!confirm('Tem certeza? Subcategorias também serão apagadas.')) return;
                    try {
                        await axios.delete(`/categorias/${id}`);
                        showMessage('Categoria removida.', 'success');
                        fetchCategories();
                    } catch (error) {
                        console.error(error);
                        showMessage('Erro ao excluir.', 'error');
                    }
                };

                
                const editCategory = (item) => {
                    isEditing.value = true;
                    form.value = { ...item, parent_id: item.parent_id };
                    showMobileMenu.value = true;
                };

                const resetForm = () => {
                    isEditing.value = false;
                    form.value = { id: null, name: '', description: '', parent_id: null };
                };

                const showMessage = (text, type) => {
                    message.value = { text, type };
                    setTimeout(() => message.value.text = '', 3000);
                };

                provide('editCategory', editCategory);
                provide('deleteCategory', deleteCategory);

                // Mock
                const useMockData = () => {
                    categories.value = [{ id: 1, name: 'Erro na Conexão', description: 'Verifique o console', children: [] }];
                };

                const flatCategories = computed(() => {
                    const result = [];
                    const flatten = (list) => {
                        list.forEach(cat => {
                            result.push({ id: cat.id, name: cat.name });
                            if (cat.children) flatten(cat.children);
                        });
                    };
                    flatten(categories.value);
                    return result;
                });

                const filteredCategories = computed(() => {
                    if (!searchQuery.value) return categories.value;
                    const query = searchQuery.value.toLowerCase();
                    const filterTree = (nodes) => {
                        return nodes.reduce((acc, node) => {
                            const newNode = { ...node };
                            if (newNode.children) newNode.children = filterTree(newNode.children);
                            if (newNode.name.toLowerCase().includes(query) || (newNode.children && newNode.children.length)) {
                                acc.push(newNode);
                            }
                            return acc;
                        }, []);
                    };
                    return filterTree(categories.value);
                });

                onMounted(() => {
                    configureAxios();
                    fetchCategories();
                });

                return {
                    categories, loading, searchQuery, showMobileMenu, isEditing, form, message,
                    submitForm, resetForm, flatCategories, filteredCategories
                };
            }
        });

        app.component('tree-item', TreeItemComponent);
        
        app.mount('#app');
    </script>
</body>
</html>