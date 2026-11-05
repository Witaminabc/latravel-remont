import { ref } from 'vue'
import axios from 'axios'

function authHeaders() {
    const token = localStorage.getItem('token')
    return token ? { Authorization: `Bearer ${token}` } : {}
}

export function useAcfGroups(apiPrefix = '/api/admin/acf') {
    const groups = ref([])
    const loading = ref(false)

    const fetchGroups = async ({ postType = null, slug = null, template = null } = {}) => {
        loading.value = true
        try {
            const { data } = await axios.get(`${apiPrefix}/groups`, {
                params: { post_type: postType, slug, template },
                headers: authHeaders(),
            })
            groups.value = data.data ?? data
            return groups.value
        } finally {
            loading.value = false
        }
    }

    return { groups, loading, fetchGroups }
}
