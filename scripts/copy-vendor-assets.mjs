#!/usr/bin/env node
/**
 * Copia os assets de terceiros de node_modules para public/vendor.
 *
 * Os ícones Phosphor vinham de `unpkg.com` a cada carregamento de página. Servir
 * do próprio domínio remove uma dependência de terceiros em tempo de execução
 * (a página não quebra se o CDN cair), elimina a necessidade de Subresource
 * Integrity numa URL sem versão e mantém o projeto funcionando offline.
 *
 * Os arquivos CSS do Phosphor referenciam as fontes por caminho relativo, então
 * cada variante é copiada com o diretório inteiro.
 */

import { cp, mkdir, rm, access } from 'node:fs/promises'
import { dirname, join } from 'node:path'
import { fileURLToPath } from 'node:url'

const projectRoot = join(dirname(fileURLToPath(import.meta.url)), '..')
const phosphorSource = join(projectRoot, 'node_modules', '@phosphor-icons', 'web', 'src')
const phosphorTarget = join(projectRoot, 'public', 'vendor', 'phosphor')

// Só as variantes usadas nas views: `ph` (regular) e `ph-fill`.
const VARIANTS = ['regular', 'fill']

async function exists(path) {
  try {
    await access(path)
    return true
  } catch {
    return false
  }
}

async function main() {
  if (!(await exists(phosphorSource))) {
    console.error('✗ @phosphor-icons/web não encontrado. Rode `npm install` primeiro.')
    process.exit(1)
  }

  await rm(phosphorTarget, { recursive: true, force: true })
  await mkdir(phosphorTarget, { recursive: true })

  for (const variant of VARIANTS) {
    await cp(join(phosphorSource, variant), join(phosphorTarget, variant), {
      recursive: true,
    })
    console.log(`✓ ícones Phosphor (${variant}) → public/vendor/phosphor/${variant}`)
  }
}

await main()
