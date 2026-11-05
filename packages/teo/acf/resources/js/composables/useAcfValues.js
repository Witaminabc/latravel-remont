import { ref } from 'vue'
import axios from 'axios'

function authHeaders() {
    const token = localStorage.getItem('token')
    return token ? { Authorization: `Bearer ${token}` } : {}
}

export function useAcfValues(apiPrefix = '/api/admin/acf') {
    const values = ref({})
    const loading = ref(false)

    const fetchValues = async (type, id) => {
        loading.value = true
        try {
            const { data } = await axios.get(`${apiPrefix}/values/${type}/${id}`, { headers: authHeaders() })
            values.value = data.data ?? data ?? {}
            return values.value
        } finally {
            loading.value = false
        }
    }

    const saveValues = async (type, id, payload = values.value) => {
        const { data } = await axios.put(
            `${apiPrefix}/values/${type}/${id}`,
            { values: payload },
            { headers: authHeaders() }
        )
        values.value = data.data ?? data ?? {}
        return values.value
    }

    return { values, loading, fetchValues, saveValues }
}
