import { StrictMode } from 'react'
import { createRoot } from 'react-dom/client'
import '../index.css'
import InferenceLogsPage from '../pages/InferenceLogsPage'

createRoot(document.getElementById('root')).render(
  <StrictMode>
    <InferenceLogsPage />
  </StrictMode>,
)
