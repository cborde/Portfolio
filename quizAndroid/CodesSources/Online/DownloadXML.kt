package com.example.cocoquizz.Online

import android.os.AsyncTask

import com.example.cocoquizz.Objets.Question
import com.example.cocoquizz.Objets.Quizz
import com.example.cocoquizz.Objets.Reponse

import org.w3c.dom.Element
import org.w3c.dom.NodeList
import org.xml.sax.InputSource

import java.net.URL
import java.util.ArrayList

import javax.xml.parsers.DocumentBuilderFactory

class DownloadXML : AsyncTask<String, Void, Quizz>() {

    lateinit var racine: NodeList

    override fun doInBackground(vararg Url: String): Quizz? {
        try {
            val url = URL(Url[0])
            val dbf = DocumentBuilderFactory.newInstance()
            val db = dbf.newDocumentBuilder()

            val doc = db.parse(InputSource(url.openStream()))
            doc.documentElement.normalize()
            racine = doc.getElementsByTagName("Quizzs")

            val themes = ArrayList<String>()
            val qst = ArrayList<Question>()
            val rep = ArrayList<Reponse>()
            var id_qst = 0
            var id_rep = 0

            val childR = racine.item(0).childNodes
            val nbThemes = childR.length

            var count_theme = 0
            for (i in 0 until nbThemes) {
                if (childR.item(i).nodeType == 1.toShort()) {

                    val t = childR.item(i) as Element

                    themes.add(t.getAttribute("type"))

                    val questions = t.getElementsByTagName("Question")
                    val nbQuestionsElements = questions.length

                    for (j in 0 until nbQuestionsElements) {

                        val question = questions.item(j) as Element

                        val propositions = question.getElementsByTagName("Proposition")
                        val nbPropositionsElements = propositions.length

                        //Propositions
                        for (k in 0 until nbPropositionsElements) {
                            rep.add(
                                Reponse(
                                    id_rep,
                                    propositions.item(k).textContent.replace(
                                        "\t".toRegex(),
                                        ""
                                    ).replace("\n".toRegex(), ""),
                                    id_qst
                                )
                            )
                            id_rep++
                        }

                        //Good answer
                        val indiceReponse =
                            question.getElementsByTagName("Reponse").item(0) as Element
                        val repOK = Integer.valueOf(indiceReponse.getAttribute("valeur"))

                        //Add question with the good answer
                        val q = Question(
                            id_qst,
                            question.firstChild.nodeValue.replace("\t".toRegex(), "").replace(
                                "\n".toRegex(), ""
                            ),
                            repOK
                        )
                        q.id_theme = count_theme
                        qst.add(q)
                        id_qst++
                    }
                    count_theme++
                }
            }

            return Quizz(themes, qst, rep)

        } catch (e: Exception) {
            println("Erreur : $e")
        }

        return null
    }

}
