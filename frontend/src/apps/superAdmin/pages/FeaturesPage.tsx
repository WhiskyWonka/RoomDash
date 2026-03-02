import { SectionHeader } from "@/components/ui/8bit/blocks/SectionHeader";
import { Button } from "@/components/ui/8bit/button";
import type { Feature } from "@/types/feature";
import { featuresApi } from "../services/featuresApi";
import { useEffect, useState } from "react";
import { FeatureTable } from "@/components/ui/8bit/blocks/features/FeatureTable";
import { FeatureDialog } from "@/components/ui/8bit/blocks/features/FeatureDialog";
import { DeleteFeatureDialog } from "@/components/ui/8bit/blocks/features/DeleteFeatureDialog";

export default function FeaturesPage() {

    const [features, setFeatures] = useState<Feature[]>([]);
    const [dialogOpen, setDialogOpen] = useState(false);
    const [deleteOpen, setDeleteOpen] = useState(false);
    const [editing, setEditing] = useState<Feature | null>(null);
    const [deleting, setDeleting] = useState<Feature | null>(null);

    const load = () => {
        featuresApi.list().then(setFeatures);
    };

    useEffect(load, []);

    const handleCreate = () => {
        setEditing(null);
        setDialogOpen(true);
    };

    const handleEdit = (t: Feature) => {
        setEditing(t);
        setDialogOpen(true);
    };

    const handleDelete = (t: Feature) => {
        setDeleting(t);
        setDeleteOpen(true);
    };

    const handleSubmit = async (name: string, slug: string, description: string) => {
        try {
            if (editing) {
                await featuresApi.update(editing.id, { name, slug, description });
            } else {
                await featuresApi.create({ name, slug, description });
            }
            setDialogOpen(false);
            load();
        } catch (error) {
            console.error("CRITICAL_ERROR: API_REQUEST_FAILED", error);
        }
    };

    const handleConfirmDelete = async () => {
        if (deleting) {
            await featuresApi.delete(deleting.id);
        }
        setDeleteOpen(false);
        load();
    };
    
    return (
        <div className="">
            <SectionHeader action={<Button variant="outline" onClick={handleCreate}>[+] ADD_NEW_FEATURE</Button>} />

            <div className="">
                <FeatureTable
                    features={features}
                    onEdit={handleEdit}
                    onDelete={handleDelete}
                />
            </div>

            {/* Diálogos de acción */}
            <FeatureDialog
                open={dialogOpen}
                feature={editing}
                onClose={() => setDialogOpen(false)}
                onSubmit={handleSubmit}
            />
            <DeleteFeatureDialog
                open={deleteOpen}
                feature={deleting}
                onClose={() => setDeleteOpen(false)}
                onConfirm={handleConfirmDelete}
            />
        </div>
    );
  return (
    <div className="p-8 font-mono bg-zinc-900 min-h-screen text-green-400">
      <h1 className="text-4xl mb-8 uppercase italic [text-shadow:4px_4px_0px_#000]">
        {">"} Master_Feature_List
      </h1>

      {/* CRUD TABLE */}
      <div className={`bg-zinc-800 p-6 mb-10`}>
        <table className="w-full text-left border-collapse">
          <thead>
            <tr className="border-b-4 border-black text-white">
              <th className="p-2 underline">CODE_NAME</th>
              <th className="p-2 underline">DESCRIPTION</th>
              <th className="p-2 underline">TYPE</th>
              <th className="p-2 underline text-right">ACTIONS</th>
            </tr>
          </thead>
          <tbody>
            <tr className="hover:bg-zinc-700 transition-colors">
              <td className="p-2 font-bold text-yellow-400">POS_SYSTEM</td>
              <td className="p-2">Habilita el punto de venta del bar.</td>
              <td className="p-2 italic text-blue-300">Boolean</td>
              <td className="p-2 text-right">
                <button className="mr-2 text-red-500">[BORRAR]</button>
                <button className="text-white">[EDITAR]</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <button>
        + CREATE_NEW_FEATURE
      </button>
    </div>
  )
}